<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:RepariereBlocksMitVorganger',
    description: 'Add a short description for your command',
)]
class RepariereBlocksMitVorgangerCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addArgument('blocks', InputArgument::REQUIRED, 'Add Blocks in array notation [id1,id2,....]')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $arg1 = $input->getArgument('blocks');

        if ($arg1) {
            $io->note(sprintf('You passed an argument: %s', $arg1));
        }



        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return Command::SUCCESS;
    }
}
