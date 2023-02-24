<?php

namespace App\Command;

use App\Entity\Active;
use App\Service\ChildSearchService;
use App\Service\CopyChildToNewSchuljahr;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;


class CopyChildsCommand extends Command
{
    protected static $defaultName = 'app:copy:childs';
    private $em;
    private CopyChildToNewSchuljahr $copyChildToNewSchuljahr;
    private ChildSearchService $childSearchService;

    public function __construct(EntityManagerInterface $entityManager, CopyChildToNewSchuljahr $copyChildToNewSchuljahr, ChildSearchService $childSearchService, string $name = null)
    {
        parent::__construct($name);
        $this->em = $entityManager;
        $this->copyChildToNewSchuljahr = $copyChildToNewSchuljahr;
        $this->childSearchService = $childSearchService;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Add a short description for your command')
            ->addArgument('source', null, InputOption::VALUE_NONE, 'Schuljahr von welchem die Kinder kopiert werden')
            ->addArgument('target', null, InputOption::VALUE_NONE, 'Schuljahr in welches die Kinder kopiert werden')
            ->addArgument('date', null, InputOption::VALUE_NONE, 'Stichtag, zu welchem die Kinder kopiert werden sollen')
            ->addArgument('schuljahrsMatrix', null, InputOption::VALUE_NONE, 'Wie sollen sich die Schuljahre ändern, wie sollen diese hochgezählt werden')
            ->addArgument('blockmatrix', null, InputOption::VALUE_NONE, 'Soll die MIedglidschaft in einem BLock zu einem Block im nächsten Shculjahr gemappt werden, kann diese matrix hier angegeben werden {"start_id":["end_id1","end_id2"....]}');
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
        if ($stichtag < $sourceActive->getVon() || $stichtag > $sourceActive->getBis()) {
            $io->error('Stichtag liegt außerhalb des Schuljahrs');
            return Command::FAILURE;
        }

        if ($input->getArgument('schuljahrsMatrix')) {
            $matrix = json_decode($input->getArgument('schuljahrsMatrix'), true);
        }
        if ($input->getArgument('schuljahrsMatrix')) {
            $blockMatrix = json_decode($input->getArgument('blockmatrix'), true);
        } else {
            $blockMatrix = array();
        }
        $kinderTarget = $this->childSearchService->searchChild(array('schuljahr' => $targetActive), null, false, null, $targetActive->getVon(), null, $sourceActive->getStadt());
        if (sizeof($kinderTarget) > 0) {
            $helper = $this->getHelper('question');
            $question = new ConfirmationQuestion(sprintf('Es existieren bereits %s Kinder in dem ZielSchuljahr. Wollen Sie fortfahren?',sizeof($kinderTarget)), false,
                '/^(y|j)/i');

            if (!$helper->ask($input, $output, $question)) {
                $io->info('We stop the transfer.');
                return Command::SUCCESS;
            }

        }

        $res = $this->copyChildToNewSchuljahr->copyKinderToSchuljahr($sourceActive, $targetActive, $stichtag, $matrix, $blockMatrix, $output);
        if (!$res) {
            $io->error('Target ist nicht mehr leer. Es wurden keine Kinder kopiert');
            return Command::FAILURE;
        }

        $io->success('We copy a lot of children');

        return Command::SUCCESS;
    }
}
