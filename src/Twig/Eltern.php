<?php
// src/Twig/AppExtension.php
namespace App\Twig;

use App\Controller\LoerrachWorkflowController;
use App\Entity\Active;
use App\Entity\Kind;
use App\Entity\Schule;
use App\Entity\Stammdaten;
use App\Entity\Zeitblock;
use App\Service\ElternService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class Eltern extends AbstractExtension
{


    private ElternService $elternService;

    public function __construct(ElternService $elternService)
    {

        $this->elternService = $elternService;
    }

    public function getFunctions()
    {
        return array(
            new TwigFunction('getEltern', array($this, 'getEltern')),
            new TwigFunction('getAllKinderWithHistory', array($this, 'getAllKinderWithHistory')),
            new TwigFunction('getGetEarliestChildOfStammdaten', array($this, 'getGetEarliestChildOfStammdaten')),
            new TwigFunction('getKinderFromStammdatenAnStichtag', array($this, 'getKinderFromStammdatenAnStichtag')),
        );
    }

    public function getEltern(Kind $kind,\DateTime $dateTime = null)
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
