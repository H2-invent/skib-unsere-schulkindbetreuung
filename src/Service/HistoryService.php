<?php

namespace App\Service;

use App\Entity\Kind;
use App\Entity\Stammdaten;
use Doctrine\ORM\EntityManagerInterface;

class HistoryService
{
    private $em;
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    public function getAllHistoyPointsFromKind(Kind $kind)
    {

        $history = $this->em->getRepository(Stammdaten::class)->findHistoryStammdaten($kind->getEltern());
        $historydate = array();
        foreach ($history as $data) {
            if ($data->getStartDate()) {
                $historydate[] = $data->getStartDate();
            }
            foreach ($data->getKinds() as $data2) {
                if ($data2->getStartDate()) {
                    $historydate[] = $data2->getStartDate();
                }
            }
        }

        $historydate = array_unique($historydate, SORT_REGULAR);

        usort($historydate, function (\DateTime $a, \DateTime $b) {
            return $a->format('U') < $b->format('U') ? -1 : 1;
        });
        return $historydate;
    }
    public function getAllHistoyPointsFromStammdaten(Stammdaten $stammdaten)
    {

        $history = $this->em->getRepository(Stammdaten::class)->findHistoryStammdaten($stammdaten);
        $historydate = array();
        foreach ($history as $data) {
            if ($data->getStartDate()) {
                $historydate[] = $data->getStartDate();
            }
            foreach ($data->getKinds() as $data2) {
                if ($data2->getStartDate()) {
                    $historydate[] = $data2->getStartDate();
                }
            }
        }

        $historydate = array_unique($historydate, SORT_REGULAR);

        usort($historydate, function (\DateTime $a, \DateTime $b) {
            return $a->format('U') < $b->format('U') ? -1 : 1;
        });
        return $historydate;
    }
}