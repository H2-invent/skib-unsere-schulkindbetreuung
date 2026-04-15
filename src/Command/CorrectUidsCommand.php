<?php

namespace App\Command;

use App\Entity\Stammdaten;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CorrectUidsCommand extends Command
{
    protected static $defaultName = 'app:correct:uids';

    public function __construct(
        private EntityManagerInterface $em,
        ?string $name = null,
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Corrects double uids');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $count = 0;
        $stammdaten = $this->em->getRepository(Stammdaten::class)->findAll();
        $progressBar = new ProgressBar($output, sizeof($stammdaten));
        foreach ($stammdaten as $data) {
            $progressBar->advance();
            try {
                $this->em->getRepository(Stammdaten::class)->findActualStammdatenByUid($data->getUid());
            } catch (\Exception) {
                $stammdatenLocal = $this->em->getRepository(Stammdaten::class)->findBy(['uid' => $data->getUid()]);
                $tracingArrold = null;
                $tracingDate = null;
                foreach ($stammdatenLocal as $data2) {
                    if (!$tracingDate || ($data2->getCreatedAt() && $tracingDate > $data2->getCreatedAt())) {
                        $tracingDate = $data2->getCreatedAt();
                        $tracingArrold = $data2->getTracing();
                    }
                    $data2->setUid(md5((string) $data2->getTracing()));
                    $this->em->persist($data2);
                    $io->info(sprintf('we replace uid from %s', $data2->getEmail()));
                    $count++;
                }
                foreach ($stammdatenLocal as $data2) {
                    if ($tracingArrold !== $data2->getTracing()) {
                        $data2->setTracingOfLastYear($tracingArrold);
                    }
                    $this->em->persist($data2);
                }
            }
        }
        $progressBar->finish();
        $this->em->flush();

        $io->success(sprintf('We replace %s uids', $count));

        return Command::SUCCESS;
    }
}
