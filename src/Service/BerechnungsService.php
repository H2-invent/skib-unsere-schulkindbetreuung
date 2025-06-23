<?php

namespace App\Service;

use App\Entity\Kind;
use App\Entity\Stammdaten;
use Doctrine\ORM\EntityManagerInterface;

class BerechnungsService
{

    private ElternService $elternService;
    private $withBeworben = true;
    private EntityManagerInterface $entityManager;

    public function __construct(ElternService $elternService, EntityManagerInterface $entityManager)
    {
        $this->elternService = $elternService;
        $this->entityManager = $entityManager;
    }

    public function getPreisforBetreuung(Kind $kind, $withBeworben = true, \DateTime $stichtag = null, $demo = false): float
    {
        $this->withBeworben = $withBeworben;
        $stadt = $kind->getSchule()->getStadt();
        if (!$stichtag) {
            $stichtag = new \DateTime();
        }
        $adresse = $this->elternService->getElternForSpecificTimeAndKind($kind, $stichtag, $demo);
        $kind = $this->entityManager->getRepository(Kind::class)->findLatestKindForDate($kind, $stichtag, $demo);
        $geschwister = $this->elternService->getKinderProStammdatenAnEinemZeitpunkt($adresse, $stichtag, $demo);
        unset($geschwister[$kind->getTracing()]);
        $kinder = $this->elternService->getKinderProStammdatenAnEinemZeitpunkt($adresse, $stichtag, $demo);
        $summe = 0;
        $formel = $stadt->getBerechnungsFormel();
        if ($kind->getSchuljahr() and $kind->getSchuljahr()->getSpecialCalculationFormular()){
            $formel = $kind->getSchuljahr()->getSpecialCalculationFormular();
        }
        eval($formel);
        return $summe;
    }

    private function getBetragforKindBetreuung(Kind $kind, Stammdaten $eltern)
    {
        $summe = 0;
        $blocks = $kind->getZeitblocks()->toArray();
        if ($this->withBeworben) {
            $blocks = array_merge($blocks, $kind->getBeworben()->toArray());
        }

        foreach ($blocks as $data) {
            if ($data->getGanztag() !== 0 && $data->getDeleted() === false) {
                $summe += $data->getPreise()[$eltern->getEinkommen()];
            }
        }

        return $summe;
    }

    public function getGesamtPreisProStammdatenZeitpunk(Stammdaten $stammdaten, \DateTime $dateTime)
    {
        $stammdaten = $this->entityManager->getRepository(Stammdaten::class)->findStammdatenFromStammdatenByDate($stammdaten, $dateTime);
        $kinder = $this->elternService->getKinderProStammdatenAnEinemZeitpunkt($stammdaten, $dateTime);
        $summe = 0;
        foreach ($kinder as $data) {
            $summe += $this->getPreisforBetreuung($data, false, $dateTime);
        }
        return $summe;
    }
}