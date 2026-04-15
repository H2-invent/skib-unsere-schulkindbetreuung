<?php

namespace App\Command;

use App\Entity\Kind;
use App\Service\CopyChildToNewSchuljahr;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class FixStartDateoFChildCommand extends Command
{
    protected static $defaultName = 'app:fix:wrongShoolyear';

    public function __construct(
        private EntityManagerInterface $em,
        private CopyChildToNewSchuljahr $copyChildToNewSchuljahr,
        ?string $name = null,
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this
            ->setDescription('This fixes when a workingcopy is in two shoolyears')
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $count = 0;
        $kinder = $this->em->getRepository(Kind::class)->findAll();
        $progressbar = new ProgressBar($output, sizeof($kinder));
        foreach ($kinder as $kind) {
            $progressbar->advance();
            $schuljahr = null;
            foreach ($kind->getZeitblocks() as $block) {
                if ($block->getActive() !== $schuljahr) {
                    if ($schuljahr !== null) {
                        $count++;
                        $this->copyChildToNewSchuljahr->fixChildInTwoYears($kind);
                    }
                    $schuljahr = $block->getActive();
                }
            }
        }
        $progressbar->finish();
        $io->success(sprintf('we fixed %s Childs', $count));

        return Command::SUCCESS;
    }
}
