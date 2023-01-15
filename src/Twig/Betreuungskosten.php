<?php
// src/Twig/AppExtension.php
namespace App\Twig;

use App\Controller\LoerrachWorkflowController;
use App\Entity\Active;
use App\Entity\Kind;
use App\Entity\Schule;
use App\Entity\Stammdaten;
use App\Entity\Zeitblock;
use App\Service\BerechnungsService;
use App\Service\ElternService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class Betreuungskosten extends AbstractExtension
{


    private BerechnungsService $berechnungsService;

    public function __construct(BerechnungsService $berechnungsService)
    {

        $this->berechnungsService = $berechnungsService;
    }

    public function getFunctions()
    {
        return array(
            new TwigFunction('getPreisforBetreuung', array($this, 'getPreisforBetreuung')),
            new TwigFunction('getPreisforBetreuungWithoutBeworben', array($this, 'getPreisforBetreuungWithoutBeworben')),
            new TwigFunction('getPreisforBetreuungWithoutBeworbenActual', array($this, 'getPreisforBetreuungWithoutBeworbenActual')),
        );
    }

    public function getPreisforBetreuung(Kind $kind=null,\DateTime $dateTime = null)
    {
        if (!$dateTime){
            $dateTime = new \DateTime();
        }

        return $this->berechnungsService->getPreisforBetreuung($kind, true, $dateTime,true);
    }

    public function getPreisforBetreuungWithoutBeworben(Kind $kind=null,\DateTime $dateTime = null)
    {
        if (!$dateTime){
            $dateTime = new \DateTime();
        }

        return $this->berechnungsService->getPreisforBetreuung($kind, false, $dateTime);
    }

    public function getPreisforBetreuungWithoutBeworbenActual(Stammdaten $stammdaten, \DateTime $dateTime)
    {
        return $this->berechnungsService->getGesamtPreisProStammdatenZeitpunk($stammdaten, $dateTime);
    }

}
