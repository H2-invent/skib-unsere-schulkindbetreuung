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
//        $history = $this->em->getRepository(Kind::class)->findHistoryOfThisChild($kind);
        $historydate = array();
//        foreach ($history as $data) {
//            if ($data->getStartDate()) {
//                $historydate[] = $data->getStartDate();
//            }
//            foreach ($data->getKinds() as $data2) {
//                if ($data2->getStartDate()) {
//                    $historydate[] = $data2->getStartDate();
//                }
//            }
//        }

//        $historydate = array_unique($historydate, SORT_REGULAR);
        foreach ($history as $key => $data) {

            // Überprüfen, ob getCreatedAt() ein leeres Array zurückgibt
            if (!$history[$key]->getCreatedAt() || (!$history[$key]->getStartDate() && sizeof($history[$key]->getKinds()->toArray()) === 0)) {

                unset($history[$key]);
                continue;
            }
        }

// Optional: Zurücksetzen der Schlüsselindizes
        $history = array_values($history);


        usort($history, function (Stammdaten $a, Stammdaten $b) {

            try {
                $aDate = $a->getStartDate() ?: $a->getKinds()[0]->getStartDate();
                $bDate = $b->getStartDate() ?: $b->getKinds()[0]->getStartDate();
            }catch (\Exception $exception){

            }

            if ($aDate == $bDate) {
                return $a->getCreatedAt() > $b->getCreatedAt() ? -1 : 1;
            }
            return $aDate > $bDate ? -1 : 1;
        });
        return $history;
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