<?php

namespace App\Service;

use App\Entity\Kind;
use App\Entity\Stammdaten;
use Doctrine\ORM\EntityManagerInterface;

class ElternService
{
    private $em;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }
    public function getLatestElternFromChild(Kind $kind):Stammdaten{
        if ($kind->getEltern()->getCreatedAt()) {
            return $this->em->getRepository(Stammdaten::class)->findlatestStammdatenfromKind($kind);
        } else {
            return $kind->getEltern();
        }
    }
    public function getLatestElternFromCEltern(Stammdaten $stammdaten):Stammdaten{
        if ($stammdaten->getCreatedAt()) {
            return $this->em->getRepository(Stammdaten::class)->findlatestStammdatenfromStammdaten($stammdaten);
        } else {
            return $stammdaten;
        }
    }
    public function getKindervonElternMitHistorie(Stammdaten $eltern){
        return $this->em->getRepository(Kind::class)->findAllChildrenWithHistoryFromParent($eltern);
    }
}