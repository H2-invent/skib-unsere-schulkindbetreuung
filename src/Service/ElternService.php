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

    public function getLatestElternFromChild(Kind $kind): Stammdaten
    {
        if ($kind->getEltern()->getCreatedAt()) {
            return $this->em->getRepository(Stammdaten::class)->findlatestStammdatenfromKind($kind);
        } else {
            return $kind->getEltern();
        }
    }

    public function getElternForSpecificTimeAndKind(Kind $kind, \DateTime $dateTime = null, $demo = false)
    {
        if (!$dateTime){
            $dateTime = new \DateTime();
        }
       $parent = $this->em->getRepository(Stammdaten::class)->findStammdatenfromKindforSpecificDate($kind,$dateTime,$demo);
        return $parent;
    }

    public function getElternForSpecificTimeAndStammdaten(Stammdaten $stammdaten, \DateTime $dateTime = null)
    {
        $parent = $this->em->getRepository(Stammdaten::class)->findStammdatenFromStammdatenByDate($stammdaten,$dateTime);
        return $parent;
    }


    public function getLatestElternFromCEltern(Stammdaten $stammdaten): Stammdaten
    {
        if ($stammdaten->getCreatedAt()) {
            return $this->em->getRepository(Stammdaten::class)->findlatestStammdatenfromStammdaten($stammdaten);
        } else {
            return $stammdaten;
        }
    }

    public function getKindervonElternMitHistorie(Stammdaten $eltern)
    {
        return $this->em->getRepository(Kind::class)->findAllChildrenWithHistoryFromParent($eltern);
    }


    public function getEarliestChildOfStammdaten(Stammdaten $stammdaten): ?Kind
    {
        $earliest = null;
        foreach ($stammdaten->getKinds() as $data) {
            if ($earliest === null || ($data->getStartDate() && $data->getStartDate() < $earliest->getStartDate())) {
                $earliest = $data;
            }
        }

        return $earliest;
    }

    public function getKinderProStammdatenAnEinemZeitpunkt(Stammdaten $stammdaten, \DateTime $zeitpunkt, $demo = false)
    {
        return $this->em->getRepository(Kind::class)->findKinderProStammdatenAnStichtag($stammdaten, $zeitpunkt, $demo);
    }



}