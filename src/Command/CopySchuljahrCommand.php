<?php

namespace App\Command;

use App\Entity\Active;
use App\Service\CopySchuljahr;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CopySchuljahrCommand extends Command
{
    protected static $defaultName = 'app:copySchuljahr';
    private $copySchuljahr;
    private $em;
    public function __construct( CopySchuljahr $copySchuljahr,EntityManagerInterface $entityManager, string $name = null)
    {
        parent::__construct($name);
        $this->copySchuljahr = $copySchuljahr;
        $this->em = $entityManager;
    }

    protected function configure()
    {
        $this
            ->setDescription('Copy a schuljahr with the zeitblocks')
            ->addArgument('id', InputArgument::OPTIONAL, 'This is the Id of the schuljahr you want to copy')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $id = $input->getArgument('id');

        if ($id) {
            $io->note(sprintf('We copy the Schuljahr with ID: %s', $id));
            $year = $this->em->getRepository(Active::class)->find($id);
            $this->copySchuljahr->copyYear($year);

        }


        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return 0;
    }
}
