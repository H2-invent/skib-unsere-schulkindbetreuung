<?php
// src/Twig/AppExtension.php
namespace App\Twig;

use App\Entity\Kind;
use App\Entity\Stammdaten;
use App\Service\BerechnungsService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class Betreuungskosten extends AbstractExtension
{


    public function __construct(private BerechnungsService $berechnungsService)
    {
    }

    public function getFunctions()
    {
        return array(
            new TwigFunction('getPreisforBetreuung', $this->getPreisforBetreuung(...)),
            new TwigFunction('getPreisforBetreuungWithoutBeworben', $this->getPreisforBetreuungWithoutBeworben(...)),
            new TwigFunction('getPreisforBetreuungWithoutBeworbenActual', $this->getPreisforBetreuungWithoutBeworbenActual(...)),
        );
    }

    public function getPreisforBetreuung(?Kind $kind=null,?\DateTime $dateTime = null)
    {
        if (!$dateTime){
            $dateTime = new \DateTime();
        }

        return $this->berechnungsService->getPreisforBetreuung($kind, true, $dateTime,true);
    }

    public function getPreisforBetreuungWithoutBeworben(?Kind $kind=null,?\DateTime $dateTime = null)
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
