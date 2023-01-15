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

class Stadt extends AbstractExtension
{


    private $em;

    public function __construct(EntityManagerInterface $entityManager )
    {

      $this->em = $entityManager;
    }

    public function getFunctions()
    {
        return array(
            new TwigFunction('getStadtFromEltern', array($this, 'getStadtFromEltern')),

        );
    }

   public function getStadtFromEltern(Stammdaten $stammdaten){
        return $this->em->getRepository(\App\Entity\Stadt::class)->findStadtByStammdaten($stammdaten);
   }

}
