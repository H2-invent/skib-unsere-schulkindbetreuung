<?php
// src/Twig/AppExtension.php
namespace App\Twig;

use App\Controller\LoerrachWorkflowController;
use App\Entity\Active;
use App\Entity\Kind;
use App\Entity\Schule;
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
        );
    }

    public function getPreisforBetreuung(Kind $kind)
    {
        return $this->berechnungsService->getPreisforBetreuung($kind);
    }


}
