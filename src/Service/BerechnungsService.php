<?php

namespace App\Service;

use App\Dto\ChildFeeCalculation;
use App\Entity\Kind;
use App\Entity\Stammdaten;
use Doctrine\ORM\EntityManagerInterface;

final class BerechnungsService
{
    private bool $withBeworben = true;

    public function __construct(
        private readonly ElternService $elternService,
        private readonly EntityManagerInterface $entityManager,
    )
    {
    }

    public function getPreisforBetreuung(Kind $kind, bool $withBeworben = true, ?\DateTime $stichtag = null, bool $demo = false): float
    {
        return $this->calculatePreisforBetreuung($kind, $withBeworben, $stichtag, $demo)->summe;
    }

    public function calculatePreisforBetreuung(
        Kind $kind,
        bool $withBeworben = true,
        ?\DateTime $stichtag = null,
        bool $demo = false,
    ): ChildFeeCalculation
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
        $bruttoSumme = (float) $this->getBetragforKindBetreuung($kind, $adresse);
        $summe = 0;
        $formel = $stadt->getBerechnungsFormel();
        if ($kind->getSchuljahr() and $kind->getSchuljahr()->getSpecialCalculationFormular()){
            $formel = $kind->getSchuljahr()->getSpecialCalculationFormular();
        }
        eval($formel);

        $summe = round((float) $summe, 2);
        $bruttoSumme = round($bruttoSumme, 2);

        return new ChildFeeCalculation(
            summe: $summe,
            bruttoSumme: $bruttoSumme,
            rabatt: round(max(0.0, $bruttoSumme - $summe), 2),
        );
    }

    private function getBetragforKindBetreuung(Kind $kind, Stammdaten $eltern): float
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

        return (float) $summe;
    }

    public function getGesamtPreisProStammdatenZeitpunk(Stammdaten $stammdaten, \DateTime $dateTime): float
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
