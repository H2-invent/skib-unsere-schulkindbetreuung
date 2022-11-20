<?php

namespace App\Command;

use App\Entity\Kind;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;


class MigrateStartDateCommand extends Command
{

    protected static $defaultName = 'app:migrate:startDate';
    protected static $defaultDescription = 'Add a short description for your command';
    private $em;

    public function __construct(EntityManagerInterface $entityManager, string $name = null)
    {
        parent::__construct($name);
        $this->em = $entityManager;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $io = new SymfonyStyle($input, $output);

        $kinder = $this->em->getRepository(Kind::class)->findAll();
        $progressBar = new ProgressBar($output, sizeof($kinder));

// starts and displays the progress bar
        $progressBar->start();
        $counter = 0;
        foreach ($kinder as $kind) {
            $progressBar->advance();
            if ($kind->getZeitblocks()->count() > 0) {
                if ($kind->getSaved()) {
                    $kind->setStartDate($kind->getZeitblocks()[0]->getActive()->getVon());
                    $this->em->persist($kind);
                    if ($kind->getHistory() > 0) {
                        $history = $this->em->getRepository(Kind::class)->findOneBy(array('tracing' => $kind->getTracing(),'history'=>$kind->getHistory()-1));
                        $kind->setStartDate($history->getEltern()->getEndedAt()->modify('first day of next month'));
                    }

                }
                if (!$kind->getFin() && $kind->getSaved()){
                    $kind->setStartDate(null);
                }
                $counter++;
                $this->em->persist($kind);
            }
        }
        $progressBar->finish();
        $this->em->flush();
        $io->success(sprintf('we set %d startdates',$counter));

        return Command::SUCCESS;
    }
}
