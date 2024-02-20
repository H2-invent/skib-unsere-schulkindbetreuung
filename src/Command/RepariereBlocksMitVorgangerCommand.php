<?php

namespace App\Command;

use App\Entity\Zeitblock;
use App\Repository\ActiveRepository;
use App\Repository\ZeitblockRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:repair:vorgangerBlocks',
    description: 'Add a short description for your command',
)]
class RepariereBlocksMitVorgangerCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ZeitblockRepository    $zeitblockRepository,
        private ActiveRepository       $activeRepository,
        string                         $name = null
    )
    {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this
            ->addArgument('schuljahr', InputArgument::REQUIRED, 'Schuljahr to secure this command');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $counter = 0;
        $io = new SymfonyStyle($input, $output);
        $schuljahr = $this->activeRepository->find($input->getArgument('schuljahr'));

        foreach ($schuljahr->getBlocks() as $data) {
            $block = $data;
            if ($block) {
                $io->info(sprintf('We work with block Nr.: %s', $block->getId()));
                foreach ($block->getVorganger() as $cBlock){
                    foreach ($block->getKind() as $child){
                        if (!in_array($cBlock,$child->getZeitblocks()->toArray())){
                            $child->addZeitblock($cBlock);
                            $io->info(sprintf('Child %s %s added  to Block: %s', $child->getVorname(), $child->getNachname(), $cBlock->getId()));
                            $counter++;
                        }

                        $this->entityManager->persist($child);
                    }
                    foreach ($block->getKinderBeworben() as $child){
                        $io->info(sprintf('We check Child: %s %s', $child->getVorname(), $child->getNachname()));
                        if (!in_array($cBlock,$child->getBeworben()->toArray())){
                            $child->addBeworben($cBlock);
                            $io->info(sprintf('Child %s %s added to BEWORBEN Block %s', $child->getVorname(), $child->getNachname(), $cBlock->getId()));
                        }
                        $this->entityManager->persist($child);
                        $counter++;
                    }
                }
            }
        }
        $this->entityManager->flush();
        $io->success(sprintf('We added %s Child to Blocks', $counter));

        return Command::SUCCESS;
    }
}
