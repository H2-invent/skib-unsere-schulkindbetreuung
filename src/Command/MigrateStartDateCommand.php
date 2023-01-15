<?php

namespace App\Command;

use App\Entity\Active;
use App\Entity\Kind;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
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
private $logger;
    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger, string $name = null)
    {
        parent::__construct($name);
        $this->em = $entityManager;
        $this->logger = $logger;
    }


    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        set_time_limit(6000);
        $io = new SymfonyStyle($input, $output);


        $this->em->flush();

        $kinder = $this->em->getRepository(Kind::class)->findAll();
        $progressBar = new ProgressBar($output, sizeof($kinder));

// starts and displays the progress bar
        $progressBar->start();
        $counter = 0;

        foreach ($kinder as $kind) {

            $this->logger->debug('we start the loop');

            $progressBar->advance();
            $this->logger->debug('we get the schuljahr');
            $schuljahr = $this->em->getRepository(Active::class)->findSchuljahrFromKind($kind);

            if ($schuljahr) {
                $this->logger->debug('we get the Elter from the Child');
                $eltern = $kind->getEltern();
                if ($kind->getSaved()) {
                    $this->logger->debug('we set startdate of the child');
                    $kind->setStartDate(clone $schuljahr->getVon());

                    if ($kind->getHistory() === 0) {
                        $this->logger->debug('The child is the first in a history');
                        if ($eltern->getCreatedAt() > $schuljahr->getVon()) {
                            $this->logger->debug('We set the startdate to the first day of this month if the parents are created after the start of schuljahr');
                            $kind->setStartDate((clone $eltern->getCreatedAt())->modify('first day of next month'));
                        }
                    } else {
                        if ($eltern->getCreatedAt() > $schuljahr->getVon()) {
                            $history = $this->em->getRepository(Kind::class)->findOneBy(array('tracing' => $kind->getTracing(), 'history' => $kind->getHistory() - 1));
                            $kind->setStartDate((clone $history->getEltern()->getEndedAt())->modify('first day of next month'));

                        }
                    }

                    if ($kind->getStartDate()) {
                        $eltern->setStartDate(clone $kind->getStartDate());
                        $this->logger->debug('Persis Chid');

                    }

                }
                if (!$kind->getFin() && !$kind->getSaved()) {//kind ist die woking copy
                    $kind->setStartDate(null);// setze startDate uf null

                }

                if (!$eltern->getFin() && !$eltern->getSaved() && !$kind->getStartDate()) {
                    $eltern->setCreatedAt(null);
                    $eltern->setStartDate(null);


                }
                $this->em->persist($eltern);
                $this->em->persist($kind);
                $counter++;
                $this->logger->debug('We flush all the stuff');

            }
        }
        $this->em->flush();
        $progressBar->finish();
        $io->success(sprintf('we set %d startdates for children', $counter));


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
