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
        );
    }

    public function getEltern(Kind $kind)
    {
        return $this->elternService->getLatestElternFromChild($kind);
    }
    public function getAllKinderWithHistory(Stammdaten $stammdaten)
    {
        return $this->elternService->getKindervonElternMitHistorie($stammdaten);
    }

}
