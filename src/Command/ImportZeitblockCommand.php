<?php

namespace App\Command;

use App\Entity\Active;
use App\Entity\Schule;
use App\Entity\Zeitblock;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Reader;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:import-zeitblock',
    description: 'Add a short description for your command',
)]
class ImportZeitblockCommand extends Command
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function configure(): void
    {
        $this->addArgument('csvFile', InputArgument::REQUIRED, 'Pfad zur CSV-Datei');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $csvFile = $input->getArgument('csvFile');
        $csv = Reader::createFromPath($csvFile, 'r');
        $csv->setDelimiter(';');
        $csv->setHeaderOffset(0);

        $notFoundIds = [];

        foreach ($csv as $record) {

            $id = $record['id'] ?? null;
            $preise = [];

            // Preise extrahieren (Annahme: Spalten "Preis 1", "Preis 2", ... existieren)
            foreach ($record as $key => $value) {
                if (str_starts_with($key, 'preis') && !empty($value)) {
                    $preise[] = (float) str_replace(',', '.', $value);
                }
            }

            $ganztag = $record['ganztag'] ?? null;
            $von = $record['von'] ?? null;
            $bis = $record['bis'] ?? null;
            $schulId = $record['schul_id'] ?? null;
            $schule = $this->entityManager->getRepository(Schule::class)->find($schulId);
            if (!$schule){
              $output->writeln('<error>Schule nicht gefunden</error>');
              continue;
            }

            $wochentag= $record['wochentag'] ?? null;
            $schuljahrId= $record['schuljahr'] ?? null;
            $schuljahr = $this->entityManager->getRepository(Active::class)->find($schuljahrId);
            if ($id) {
                $zeitblock = $this->entityManager->getRepository(Zeitblock::class)->find($id);
                if (!$zeitblock) {
                    $notFoundIds[] = $id;
                    continue;
                }
            } else {
                $zeitblock = new Zeitblock();
                $zeitblock->setDeleted(false)
                    ->setMin(0)
                    ->setMax(0)
                    ->setDeaktiviert(false)
                    ->setHidePrice(false);
            }

            $zeitblock->setPreise($preise);
            $zeitblock->setGanztag($ganztag);
            $zeitblock->setVon(new \DateTime($von));
            $zeitblock->setBis(new \DateTime($bis));
            $zeitblock->setWochentag($wochentag);
            $zeitblock->setSchule($schule);
            $zeitblock->setActive($schuljahr);

            $this->entityManager->persist($zeitblock);
        }

        $this->entityManager->flush();

        if (!empty($notFoundIds)) {
            $output->writeln('<error>Folgende IDs wurden nicht gefunden: ' . implode(', ', $notFoundIds) . '</error>');
        }

        $output->writeln('<info>Import abgeschlossen!</info>');

        return Command::SUCCESS;
    }
}
