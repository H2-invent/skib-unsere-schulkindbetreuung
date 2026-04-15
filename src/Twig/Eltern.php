<?php
// src/Twig/AppExtension.php
namespace App\Twig;

use App\Entity\Kind;
use App\Entity\Stammdaten;
use App\Service\ElternService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class Eltern extends AbstractExtension
{


    public function __construct(private ElternService $elternService)
    {
    }

    public function getFunctions()
    {
        return array(
            new TwigFunction('getEltern', $this->getEltern(...)),
            new TwigFunction('getAllKinderWithHistory', $this->getAllKinderWithHistory(...)),
            new TwigFunction('getGetEarliestChildOfStammdaten', $this->getGetEarliestChildOfStammdaten(...)),
            new TwigFunction('getKinderFromStammdatenAnStichtag', $this->getKinderFromStammdatenAnStichtag(...)),
        );
    }

    public function getEltern(Kind $kind,?\DateTime $dateTime = null)
    {
        if (!$dateTime){
            return $kind->getEltern();
        }

        $eltern =  $this->elternService->getElternForSpecificTimeAndKind($kind,$dateTime);

        return $eltern;
    }

    public function getAllKinderWithHistory(Stammdaten $stammdaten)
    {
        return $this->elternService->getKindervonElternMitHistorie($stammdaten);
    }

    public function getGetEarliestChildOfStammdaten(Stammdaten $stammdaten)
    {
        $res = $this->elternService->getEarliestChildOfStammdaten($stammdaten);
        return $res;
    }

    public function getKinderFromStammdatenAnStichtag(Stammdaten $stammdaten, \DateTime $stichtag)
    {
        return $this->elternService->getKinderProStammdatenAnEinemZeitpunkt($stammdaten, $stichtag);
    }

}
