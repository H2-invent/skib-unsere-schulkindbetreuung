<?php

namespace App\Service;

use App\Entity\Kind;
use App\Entity\Stammdaten;
use Doctrine\ORM\EntityManagerInterface;

class BerechnungsService
{

    private ElternService $elternService;
    private $withBeworben = true;
    private EntityManagerInterface  $entityManager;
    public function __construct(ElternService $elternService, EntityManagerInterface $entityManager)
    {
        $this->elternService = $elternService;
        $this->entityManager = $entityManager;
    }

    public function getPreisforBetreuung(Kind $kind, $withBeworben = true):float
    {
        $this->withBeworben = $withBeworben;
        $stadt = $kind->getSchule()->getStadt();
        $adresse = $this->elternService->getLatestElternFromChild($kind);
        $kind = clone $kind;
        $kind->setEltern($adresse);
        $summe = 0;
        eval($stadt->getBerechnungsFormel());
        return $summe;
    }

    private function getBetragforKindBetreuung(Kind $kind, Stammdaten $eltern)
    {
        $summe = 0;
        $blocks = $kind->getZeitblocks()->toArray();
        if ($this->withBeworben){
            $blocks = array_merge($blocks, $kind->getBeworben()->toArray());
        }

        foreach ($blocks as $data) {
            if ($data->getGanztag() !== 0 && $data->getDeleted() === false) {
                $summe += $data->getPreise()[$eltern->getEinkommen()];
            }
        }
        return $summe;
    }

    public function getGesamtPreisProStammdatenZeitpunk(Stammdaten $stammdaten,\DateTime $dateTime){
        $kinder = array();
        foreach ( $stammdaten->getKinds() as $kind){
            $k =  $this->entityManager->getRepository(Kind::class)->findLatestKindForDate($kind,$dateTime);
            if ($k){
                $kinder[] = $k;
            }
        }
        $summe  = 0;
        foreach ($kinder as $data){
            $summe += $this->getPreisforBetreuung($data,false);
        }
        return $summe;
    }
}