<?php

namespace App\Command;

use App\Entity\Active;
use App\Service\ChildSearchService;
use App\Service\CopyChildToNewSchuljahr;
use App\Service\ElternService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

class ResendConfirmationCommand extends Command
{
    protected static $defaultName = 'app:resend:confirmation';

    protected function configure(): void
    {
        $this
            ->setDescription('Verschickt die Buchungsbestätigung an alle Kinder einer Schuljahres neu')
            ->addArgument('schuljahr', InputArgument::REQUIRED, 'Schuljahre-ID')
            ->addArgument('text', InputArgument::REQUIRED, 'Text welcher oben in der E-Mail angezeigt wird');
    }

    public function __construct(
        private CopyChildToNewSchuljahr $copyChildService,
        private ChildSearchService $childSearchService,
        private ElternService $elternService,
        private EntityManagerInterface $em,
        ?string $name = null,
    ) {
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $schuljahr = $this->em->getRepository(Active::class)->find($input->getArgument('schuljahr'));
        $text = $input->getArgument('text');
        if (!$schuljahr) {
            $io->error('Kein Schuljahr gefunden');

            return Command::FAILURE;
        }
        $io->info(sprintf('Schuljahr von %s bis %s der Stadt %s', $schuljahr->getVon()->format('d.m.Y'), $schuljahr->getBis()->format('d.m.Y'), $schuljahr->getStadt()->getName()));
        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion('Wollen Sie an alle Kinder in diesem Schuljahr die Buchungsbestätigung erneut zusenden? (y/n)', false,
            '/^(y|j)/i');

        if (!$helper->ask($input, $output, $question)) {
            $io->info('Nichts gesendet');

            return Command::SUCCESS;
        }

        $kinder = $this->childSearchService->searchChild(['schuljahr' => $schuljahr->getId()], null, false, null, $schuljahr->getVon(), null, $schuljahr->getStadt());
        $progressBar = new ProgressBar($output, sizeof($kinder));
        foreach ($kinder as $data) {
            $progressBar->advance();

            $eltern = $this->elternService->getElternForSpecificTimeAndKind($data, $schuljahr->getVon());
            $this->copyChildService->sendAnmedebestaetigung($data, $eltern, $schuljahr->getStadt(), $text, true);
        }

        $progressBar->finish();

        return Command::SUCCESS;
    }
}
