<?php

namespace App\Command;

use App\Entity\Active;
use App\Entity\Kind;
use App\Entity\Stadt;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;


class FixSchultypAfterMigrationCommand extends Command
{
    private $em;
    protected static $defaultName = 'app:fix:schultypAfterMigration';

    public function __construct(EntityManagerInterface $entityManager, string $name = null)
    {
        parent::__construct($name);
        $this->em = $entityManager;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Repariert die Schularten eins Kindes, das ein Schuljahr durch die Migration geändert hat. WICHTIG es wird nicht anpasst wenn das Kind Blöcke aus mehreren Schularten hat (Halbtag und Ganztag)')
            ->addArgument('stadtId', InputArgument::REQUIRED, 'ID der Stadt auf welchen sich der Fix bezieht')
            ->addArgument('schuljahrID', InputArgument::REQUIRED, 'ID des Schuljahrs auf welchen sich der Fix bezieht');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $progress = new ProgressBar($output);


        $schulJahrId = $input->getArgument('schuljahrID');
        $stadtId = $input->getArgument('stadtId');
        $stadt = $this->em->getRepository(Stadt::class)->find($stadtId);
        if (!$stadt){
            $io->error('No City found');
            return Command::FAILURE;
        }
        $schuljahr = $this->em->getRepository(Active::class)->findOneBy(array('stadt'=>$stadt, 'id'=>$schulJahrId));
        if (!$schuljahr){
            $io->error('No Schuljahr found');
            return Command::FAILURE;
        }
        $counter = 0;
        $counterFail = 0;
        $kinder = $this->em->getRepository(Kind::class)->findAll();
        $progress->start(sizeof($kinder));
        foreach ($kinder as $kind) {
            if ($kind->getSchule()->getStadt() === $stadt) {
                if ($this->em->getRepository(Active::class)->findSchuljahrFromKind($kind) === $schuljahr) {
                    $type = null;
                    foreach ($kind->getZeitblocks() as $data) {
                        if (!$type) {
                            $type = $data->getGanztag();
                        } else {
                            if ($type !== $data->getGanztag()) {
                                $type = null;
                                $counterFail++;
                                $io->info(sprintf('Kind nicht eindeutig %s',$kind->getVorname() .' '.$kind->getNachname()));
                                break;
                            }
                        }
                    }
                    if ($type) {
                        if ($type !== $kind->getArt()) {
                            $kind->setArt($type);
                            $this->em->persist($kind);
                            $counter++;
                        }
                    }
                }
            }
            $progress->advance();
        }
        $this->em->flush();
        $progress->finish();
        $io->success(sprintf('We change %s Childs and we have %s childs with an error', $counter,$counterFail));

        return Command::SUCCESS;
    }
}
