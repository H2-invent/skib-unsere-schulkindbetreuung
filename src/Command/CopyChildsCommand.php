<?php

namespace App\Command;

use App\Entity\Active;
use App\Service\CopyChildToNewSchuljahr;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:copy:childs',
    description: 'Add a short description for your command',
)]
class CopyChildsCommand extends Command
{
    private $em;
    private CopyChildToNewSchuljahr $copyChildToNewSchuljahr;

    public function __construct(EntityManagerInterface $entityManager, CopyChildToNewSchuljahr $copyChildToNewSchuljahr, string $name = null)
    {
        parent::__construct($name);
        $this->em = $entityManager;
        $this->copyChildToNewSchuljahr = $copyChildToNewSchuljahr;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('source', null, InputOption::VALUE_NONE, 'Schuljahr von welchem die Kinder kopiert werden')
            ->addArgument('target', null, InputOption::VALUE_NONE, 'Schuljahr in welches die Kinder kopiert werden')
            ->addArgument('date', null, InputOption::VALUE_NONE, 'Stichtag, zu welchem die Kinder kopiert werden sollen')
            ->addArgument('schuljahrsMatrix', null, InputOption::VALUE_NONE, 'Wie sollen sich die Schuljahre ändern, wie sollen diese hochgezählt werden');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $sourceActive = null;
        $targetActive = null;
        $stichtag = null;
        $matrix = null;


        if ($input->getArgument('source')) {
            $sourceActive = $this->em->getRepository(Active::class)->find($input->getArgument('source'));
        }
        if ($input->getArgument('target')) {
            $targetActive = $this->em->getRepository(Active::class)->find($input->getArgument('target'));
        }
        if ($sourceActive->getStadt() !== $targetActive->getStadt()) {
            $io->error('Schuljahre passen nicht zur selben Stadt');
            return Command::FAILURE;
        }

        if ($input->getArgument('date')) {
            $stichtag = new \DateTime($input->getArgument('date'));
        }
        if ($stichtag < $sourceActive->getVon() || $stichtag > $sourceActive->getBis()){
            $io->error('Stichtag liegt außerhalb des Schuljahrs');
            return Command::FAILURE;
        }

        if ($input->getArgument('schuljahrsMatrix')) {
            $matrix = json_decode($input->getArgument('schuljahrsMatrix'),true);
        }

        $res = $this->copyChildToNewSchuljahr->copyKinderToSchuljahr($sourceActive,$targetActive,$stichtag,$matrix,$output);
        if (!$res){
            $io->error('Target ist nicht mehr leer. Es wurden keine Kinder kopiert');
            return Command::FAILURE;
        }

        $io->success('We copy a lot of children');

        return Command::SUCCESS;
    }
}
