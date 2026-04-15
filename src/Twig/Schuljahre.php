<?php

// src/Twig/AppExtension.php

namespace App\Twig;

use App\Entity\Active;
use App\Entity\Stadt;
use Doctrine\ORM\EntityManagerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class Schuljahre extends AbstractExtension
{
    private $now;

    public function __construct(
        private EntityManagerInterface $em,
    ) {
        $this->now = new \DateTime();
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('getLaufendSchuljahr', $this->getLaufendSchuljahr(...)),
            new TwigFunction('getAnmeldeSchuljahr', $this->getAnmeldeSchuljahr(...)),
        ];
    }

    public function getAnmeldeSchuljahr(Stadt $stadt)
    {
        return $this->em->getRepository(Active::class)->findAnmeldeSchuljahreFromCity($stadt);
    }

    public function getLaufendSchuljahr(Stadt $stadt)
    {
        return $this->em->getRepository(Active::class)->findLaufendeSchuljahreFromCity($stadt);
    }
}
