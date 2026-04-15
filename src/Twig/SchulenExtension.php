<?php
// src/Twig/AppExtension.php
namespace App\Twig;

use App\Entity\Kind;
use App\Entity\Schule;
use App\Entity\Zeitblock;
use Doctrine\ORM\EntityManagerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class SchulenExtension extends AbstractExtension
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function getFunctions()
    {
        return array(
            new TwigFunction('getAnzahlBeworben', $this->getAnzahlBeworben(...)),
            new TwigFunction('getAnzahlBeworbenKids', $this->getAnzahlBeworbenKids(...)),
        );
    }

    public function getAnzahlBeworben(Schule $schule)
    {

        try {
            $blocks = $this->em->getRepository(Zeitblock::class)->findBeworbenBlocksBySchule($schule);
        }catch (\Exception){
            $blocks = array();
        }

        return $blocks;
    }
    public function getAnzahlBeworbenKids(Zeitblock $block)
    {

        try {
            $kids = $this->em->getRepository(Kind::class)->findBeworbenByZeitblock($block);
        }catch (\Exception){
            $kids = array();
        }

        return $kids;
    }
}
