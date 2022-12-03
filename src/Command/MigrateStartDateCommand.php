<?php

namespace App\Command;

use App\Entity\Kind;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;


class MigrateStartDateCommand extends Command
{

    protected static $defaultName = 'app:migrate:startDate';
    protected static $defaultDescription = 'Migrate Startdate from old to new version';
    private $em;

    public function __construct(EntityManagerInterface $entityManager, string $name = null)
    {
        parent::__construct($name);
        $this->em = $entityManager;
    }



    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        set_time_limit(6000);
        $io = new SymfonyStyle($input, $output);

        $kinder = $this->em->getRepository(Kind::class)->findAll();
        $progressBar = new ProgressBar($output, sizeof($kinder));

// starts and displays the progress bar
        $progressBar->start();
        $counter = 0;
        foreach ($kinder as $kind) {
            $progressBar->advance();
            if ($kind->getZeitblocks()->count() > 0) {
                if ($kind->getSaved()) {
                    $kind->setStartDate(clone $kind->getZeitblocks()[0]->getActive()->getVon());

                    if ($kind->getHistory() === 0){
                        if ($kind->getEltern()->getCreatedAt() > $kind->getZeitblocks()[0]->getActive()->getVon()){
                            $kind->setStartDate((clone  $kind->getEltern()->getCreatedAt())->modify('first day of next month'));
                        }
                    } else {
                        if ($kind->getEltern()->getCreatedAt() > $kind->getZeitblocks()[0]->getActive()->getVon()){
                            $history = $this->em->getRepository(Kind::class)->findOneBy(array('tracing' => $kind->getTracing(),'history'=>$kind->getHistory()-1));
                            $kind->setStartDate(( clone  $history->getEltern()->getEndedAt())->modify('first day of next month'));
                        }
                    }
                    $this->em->persist($kind);
                }
                if (!$kind->getFin() && !$kind->getSaved()){//kind ist die woking copy
                    $kind->setStartDate(null);// setze startDate uf null

                }
                if (!$kind->getEltern()->getFin() && !$kind->getEltern()->getSaved() && !$kind->getStartDate()){
                    $eltern = $kind->getEltern();
                    $eltern->setCreatedAt(null);
                    $this->em->persist($eltern);
                }
                $counter++;
                $this->em->persist($kind);
            }
        }
        $progressBar->finish();
        $this->em->flush();
        $io->success(sprintf('we set %d startdates',$counter));

        $progressBar = new ProgressBar($output, sizeof($kinder));
        $progressBar->start();

        $checked = array();
        $coutDelete = 0;
        foreach ($kinder as $kind) {
            $tracing = $kind->getTracing();
            if (!in_array($tracing, $checked)) {


                $allKindsWithTracing = $this->em->getRepository(Kind::class)->findBy(array('tracing' => $tracing));
                $checked[] = $tracing;
                $deleted = true;
                foreach ($allKindsWithTracing as $data) {
                    if ($data->getFin() && $data->getSaved()) {
                        $deleted = false;
                        break;
                    }
                }
                if ($deleted === true) {
                    foreach ($allKindsWithTracing as $data) {
                        $data->setStartDate(null);
                        $this->em->persist($data);
                    }

                    $coutDelete++;
                }

            }
            $progressBar->advance();
        }
        $this->em->flush();
        $progressBar->finish();


        $io->success(sprintf('we delete %d childs', $coutDelete));

        return Command::SUCCESS;
    }
}
