<?php

namespace App\Service;

use App\Entity\Kind;
use App\Entity\Stammdaten;

class BerechnungsService
{

    private ElternService $elternService;
    public function __construct(ElternService $elternService)
    {
        $this->elternService = $elternService;
    }

    public function getPreisforBetreuung(Kind $kind):float
    {   // Load the data from the city into the controller as $stadt

        //Include Parents in this route
        $stadt = $kind->getSchule()->getStadt();
        $adresse = $this->elternService->getLatestElternFromChild($kind);
        $kind->setEltern($adresse);
        $summe = 0;
        eval($stadt->getBerechnungsFormel());
        return $summe;
    }

    private function getBetragforKindBetreuung(Kind $kind, Stammdaten $eltern)
    {
        $summe = 0;
        $blocks = $kind->getZeitblocks()->toArray();
        $blocks = array_merge($blocks, $kind->getBeworben()->toArray());
        foreach ($blocks as $data) {
            if ($data->getGanztag() !== 0 && $data->getDeleted() === false) {
                $summe += $data->getPreise()[$eltern->getEinkommen()];
            }
        }
        return $summe;
    }
}