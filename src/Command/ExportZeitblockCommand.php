<?php

namespace App\Command;

use App\Entity\Active;
use App\Entity\Zeitblock;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Writer;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:export-zeitblock', description: 'Exportiert Zeitblöcke in eine CSV-Datei')]
class ExportZeitblockCommand extends Command
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('schuljahr', InputArgument::REQUIRED, 'Das Schuljahr für den Export')
            ->addArgument('csvFile', InputArgument::REQUIRED, 'Pfad zur CSV-Datei');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $schuljahr = $this->entityManager->getRepository(Active::class)->find($input->getArgument('schuljahr'));
        $csvFile = $input->getArgument('csvFile');

        $zeitbloecke = $this->entityManager->getRepository(Zeitblock::class)->findBy(['active' => $schuljahr]);

        if (empty($zeitbloecke)) {
            $io->warning('Keine Zeitblöcke für das Schuljahr gefunden.');
            return Command::FAILURE;
        }


        $preise_length = sizeof($zeitbloecke[0]->getPreise());

        $header = ['id', 'schule', 'schul_id', 'schuljahr', 'von', 'bis', 'zeit', 'faktor', 'wochentag', 'ganztag'];
        for ($i = 1; $i <= $preise_length; $i++) {
            $header[] = "preis $i";
        }

        $csv = Writer::createFromPath($csvFile, 'w+');
$csv->setDelimiter(';');
        $csv->insertOne($header);

        foreach ($zeitbloecke as $zeitblock) {
            $preise = implode(';', $zeitblock->getPreise());
            $data = [
                    $zeitblock->getId(),
                    $zeitblock->getSchule()->getName(),
                    $zeitblock->getSchule()->getId(),
                    $zeitblock->getActive()->getId(),
                    $zeitblock->getVon()->format('H:i:s'),
                    $zeitblock->getBis()->format('H:i:s'),
                    $zeitblock->getBis()->diff($zeitblock->getVon())->format('%H:%i:%s'),
                    '',
                    $zeitblock->getWochentag(),
                    $zeitblock->getGanztag()
            ];
            foreach ($zeitblock->getPreise() as $preis) {
                $data[] = $preis;
            }
            $csv->insertOne($data);
        }

        $io->success('Export erfolgreich!');
        $io->success(sprintf('We print %s rows to the file "%s"', count($zeitbloecke), $csvFile));
        return Command::SUCCESS;
    }
}
