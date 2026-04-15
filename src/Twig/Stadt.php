<?php
// src/Twig/AppExtension.php
namespace App\Twig;

use App\Entity\Stammdaten;
use Doctrine\ORM\EntityManagerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class Stadt extends AbstractExtension
{


    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function getFunctions()
    {
        return array(
            new TwigFunction('getStadtFromEltern', $this->getStadtFromEltern(...)),

        );
    }

   public function getStadtFromEltern(Stammdaten $stammdaten){
        return $this->em->getRepository(\App\Entity\Stadt::class)->findStadtByStammdaten($stammdaten);
   }

}
