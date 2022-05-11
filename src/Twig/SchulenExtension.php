<?php
// src/Twig/AppExtension.php
namespace App\Twig;

use App\Entity\Active;
use App\Entity\Schule;
use App\Entity\Zeitblock;
use Doctrine\ORM\EntityManagerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class SchulenExtension extends AbstractExtension
{
    private $em;
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    public function getFunctions()
    {
        return array(
            new TwigFunction('getAnzahlBeworben', array($this, 'getAnzahlBeworben')),
        );
    }

    public function getAnzahlBeworben(Schule $schule)
    {

        $schuljahr = $this->em->getRepository(Active::class)->findAnmeldeSchuljahrFromCity($schule->getStadt());
        $blocks = $this->em->getRepository(Zeitblock::class)->findBeworbenBlocksBySchuleAndSchulfahr($schule,$schuljahr);
        return $blocks;
    }
}
