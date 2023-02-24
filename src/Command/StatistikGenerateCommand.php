<?php

namespace App\Command;

use App\Entity\Active;
use App\Entity\Schule;
use App\Entity\Zeitblock;
use App\Service\ChildSearchService;
use App\Service\WidgetService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\Cache\ItemInterface;


class StatistikGenerateCommand extends Command
{
    protected static $defaultName = 'app:statistik:generate';
    protected static $defaultDescription = 'Migrate Startdate from old to new version';
    private $em;


    private WidgetService $widgetService;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger, ChildSearchService $childSearchService, WidgetService $widgetService, string $name = null)
    {
        parent::__construct($name);
        $this->em = $entityManager;
        $this->logger = $logger;
        $this->widgetService = $widgetService;
    }


    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $schulen = $this->em->getRepository(Schule::class)->findAll();
        $now = new \DateTime();
        $cache = new FilesystemAdapter();
        $io->info('Schulen Generieren');
        $progressBar = new ProgressBar($output, sizeof($schulen));
        $progressBar->start();
        foreach ($schulen as $data) {
            $cache->delete('schule_' . $data->getId());

            $cache->delete('schule_today_' . $data->getId());

            $this->widgetService->calculateSchulen($data, $now);

            $this->widgetService->calculateSchulenToday($data, $now);
            $progressBar->advance();
        }
        $progressBar->finish();
        $zeitblocks = $this->em->getRepository(Zeitblock::class)->findAll();
        $io->info('generate Zeitblocks');
        $progressBar = new ProgressBar($output, sizeof($zeitblocks));
        $progressBar->start();
        foreach ($zeitblocks as $data2) {
            $now = new \DateTime();
            if ($data2->getActive()->getBis() < $now) {
                $now = $data2->getActive()->getBis();
            }
            if ($data2->getActive()->getVon() > $now) {
                $now = $data2->getActive()->getVon();
            }
            $cache->delete('zeitblock_' . $data2->getId());
            $this->widgetService->calcBlocksNumberNow($data2, $now);
            $progressBar->advance();
        }
        $progressBar->finish();

        $activity = $this->em->getRepository(Active::class)->findAll();

        $io->info('generate All Childs per SchoolPerSchuljahr');
        $progressBar = new ProgressBar($output, sizeof($activity));
        $progressBar->start();
        foreach ($activity as $data3) {
            $now = $data3->getBis();
            $cache->delete('schuljahr_' . $data3->getId());
            $this->widgetService->calcChildsFromSchuljahrAndCity($data3, $now);
            $progressBar->advance();
        }
        $progressBar->finish();


        $io->success('We genearate a lot of cache values ;)');

        return Command::SUCCESS;
    }
}
