<?php
// src/Twig/AppExtension.php
namespace App\Twig;

use App\Controller\LoerrachWorkflowController;
use App\Entity\Active;
use App\Entity\Kind;
use App\Entity\Schule;
use App\Entity\Zeitblock;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class Kontingent extends AbstractExtension
{
    private $em;
    private $translator;
    public function __construct(EntityManagerInterface $entityManager, TranslatorInterface $translator)
    {
        $this->em = $entityManager;
        $this->translator = $translator;
    }

    public function getFunctions()
    {
        return array(
            new TwigFunction('getBerufstatig', array($this, 'getBerufstatig')),
        );
    }

    public function getBerufstatig(Kind $kind)
    {
        $workflow = new LoerrachWorkflowController($this->translator);
        return array_flip($workflow->beruflicheSituation)[$kind->getEltern()->getBeruflicheSituation()]??'Keine Angabe';

    }


}
