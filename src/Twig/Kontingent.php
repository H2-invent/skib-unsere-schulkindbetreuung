<?php
// src/Twig/AppExtension.php
namespace App\Twig;

use App\Controller\LoerrachWorkflowController;
use App\Entity\Active;
use App\Entity\Kind;
use App\Entity\Schule;
use App\Entity\Zeitblock;
use App\Service\ChildInBlockService;
use App\Service\ElternService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class Kontingent extends AbstractExtension
{

    private $translator;
    private ElternService $elternService;
    private ChildInBlockService $childInBlockService;
    public function __construct(TranslatorInterface $translator, ElternService $elternService, ChildInBlockService $childInBlockService)
    {
        $this->translator = $translator;
        $this->elternService = $elternService;
        $this->childInBlockService = $childInBlockService;
    }

    public function getFunctions()
    {
        return array(
            new TwigFunction('getBerufstatig', array($this, 'getBerufstatig')),
            new TwigFunction('getChildsOnSpecificTime', array($this, 'getChildsOnSpecificTime')),
            new TwigFunction('getChildsOnSpecificTimeAndFuture', array($this, 'getChildsOnSpecificTimeAndFuture')),
        );
    }

    public function getBerufstatig(Kind $kind)
    {
        $workflow = new LoerrachWorkflowController($this->translator);
        return array_flip($workflow->beruflicheSituation)[$this->elternService->getLatestElternFromChild($kind)->getBeruflicheSituation()] ?? 'Keine Angabe';

    }
    public function getChildsOnSpecificTime(Zeitblock $zeitblock,\DateTime $dateTime)
    {
        return $this->childInBlockService->getCurrentChildOfZeitblock($zeitblock,$dateTime);

    }
    public function getChildsOnSpecificTimeAndFuture(Zeitblock $zeitblock,\DateTime $dateTime)
    {
        return $this->childInBlockService->getCurrentChildAndFuturerChildOfZeitblock($zeitblock,$dateTime);

    }

}
