<?php
// src/Twig/AppExtension.php
namespace App\Twig;


use App\Entity\Active;
use App\Entity\Stammdaten;

use Doctrine\ORM\EntityManagerInterface;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class Schuljahre extends AbstractExtension
{


    private $em;
    private $now;
    public function __construct(EntityManagerInterface $entityManager )
    {

      $this->em = $entityManager;
      $this->now = new \DateTime();
    }

    public function getFunctions()
    {
        return array(
            new TwigFunction('getLaufendSchuljahr', array($this, 'getLaufendSchuljahr')),
            new TwigFunction('getAnmeldeSchuljahr', array($this, 'getAnmeldeSchuljahr')),
        );
    }

   public function getAnmeldeSchuljahr(\App\Entity\Stadt $stadt){
        return  $this->em->getRepository(Active::class)->findAnmeldeSchuljahreFromCity($stadt);
   }
    public function getLaufendSchuljahr(\App\Entity\Stadt $stadt){
        return  $this->em->getRepository(Active::class)->findLaufendeSchuljahreFromCity($stadt);
    }
}
