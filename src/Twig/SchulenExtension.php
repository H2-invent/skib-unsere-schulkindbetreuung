<?php
// src/Twig/AppExtension.php
namespace App\Twig;

use App\Entity\Active;
use App\Entity\Kind;
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
            new TwigFunction('getAnzahlBeworbenKids', array($this, 'getAnzahlBeworbenKids')),
            new TwigFunction('getAnzahlBeworbenTotal', array($this, 'getAnzahlBeworbenTotal')),
        );
    }

    public function getAnzahlBeworben(Schule $schule)
    {

        try {
            $blocks = $this->em->getRepository(Zeitblock::class)->findBeworbenBlocksBySchule($schule);
        }catch (\Exception $exception){
            $blocks = array();
        }

        return $blocks;
    }

    /**
     * Get total count of open applications (child-block combinations) for a school
     */
    public function getAnzahlBeworbenTotal(Schule $schule): int
    {
        try {
            return $this->em->getRepository(Kind::class)->countBeworbenBySchule($schule);
        } catch (\Exception $exception) {
            return 0;
        }
    }

    public function getAnzahlBeworbenKids(Zeitblock $block)
    {

        try {
            $kids = $this->em->getRepository(Kind::class)->findBeworbenByZeitblock($block);
        }catch (\Exception $exception){
            $kids = array();
        }

        return $kids;
    }
}
