<?php
// src/Twig/AppExtension.php
namespace App\Twig;

use App\Controller\LoerrachWorkflowController;
use App\Entity\Kind;
use App\Entity\Zeitblock;
use App\Service\ChildInBlockService;
use App\Service\ElternService;
use App\Service\WidgetService;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class Kontingent extends AbstractExtension
{

    public function __construct(private TranslatorInterface                $translator, private ElternService                      $elternService, private ChildInBlockService                $childInBlockService, private WidgetService                      $widgetService, private LoerrachWorkflowController $loerrachWorkflowController)
    {
    }

    public function getFunctions()
    {
        return array(
            new TwigFunction('getBerufstatig', $this->getBerufstatig(...)),
            new TwigFunction('getChildsOnSpecificTime', $this->getChildsOnSpecificTime(...)),
            new TwigFunction('getChildsOnSpecificTimeCached', $this->getChildsOnSpecificTimeCached(...)),
            new TwigFunction('getChildsOnSpecificTimeAndFuture', $this->getChildsOnSpecificTimeAndFuture(...)),
        );
    }

    public function getBerufstatig(Kind $kind)
    {
        $workflow = $this->loerrachWorkflowController;
        return array_flip($workflow->beruflicheSituation)[$this->elternService->getLatestElternFromChild($kind)->getBeruflicheSituation()] ?? 'Keine Angabe';

    }

    public function getChildsOnSpecificTime(Zeitblock $zeitblock, \DateTime $dateTime)
    {
        $res = $this->childInBlockService->getCurrentChildOfZeitblock($zeitblock, $dateTime);
        return $res;

    }

    public function getChildsOnSpecificTimeCached(Zeitblock $zeitblock)
    {
        $res = $this->widgetService->calcBlocksNumberNow($zeitblock);
        return $res;

    }

    public function getChildsOnSpecificTimeAndFuture(Zeitblock $zeitblock, \DateTime $dateTime)
    {
        return $this->childInBlockService->getCurrentChildAndFuturerChildOfZeitblock($zeitblock, $dateTime);

    }

}
