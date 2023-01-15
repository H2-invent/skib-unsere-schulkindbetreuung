<?php
/**
 * Created by PhpStorm.
 * User: Emanuel
 * Date: 03.10.2019
 * Time: 19:01
 */

namespace App\Service;


use App\Controller\LoerrachWorkflowController;
use App\Entity\Kind;
use App\Entity\Stadt;
use App\Entity\Stammdaten;
use App\Entity\Zeitblock;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Qipsius\TCPDFBundle\Controller\TCPDFController;
use Twig\Environment;

class BlockDeleteService
{
    private $print;
    private $tcpdf;
    private $translator;
    private $ics;
    private $templating;
    private $mailer;
    private $abgService;
    private $parameterbag;
    private $em;
    private $anmeldeService;
    private ChildInBlockService $childInBlockService;
    private ElternService $elternService;
    public function __construct(
        AnmeldeEmailService    $anmeldeEmailService,
        EntityManagerInterface $entityManager,
        ParameterBagInterface  $parameterBag,
        PrintAGBService        $printAGBService,
        PrintService           $print,
        TCPDFController        $tcpdf,
        TranslatorInterface    $translator,
        IcsService             $icsService,
        Environment            $templating,
        MailerService          $mailer,
        ChildInBlockService    $childInBlockService,
        ElternService          $elternService)
    {
        $this->print = $print;
        $this->tcpdf = $tcpdf;
        $this->translator = $translator;
        $this->ics = $icsService;
        $this->templating = $templating;
        $this->mailer = $mailer;
        $this->abgService = $printAGBService;
        $this->parameterbag = $parameterBag;
        $this->anmeldeService = $anmeldeEmailService;
        $this->em = $entityManager;
        $this->childInBlockService = $childInBlockService;
        $this->elternService = $elternService;
    }

    public function deleteBlock(Zeitblock $block)
    {

        $block->setDeleted(true);
        foreach ($block->getNachfolger() as $data) {
            $block->removeNachfolger($data);
        }
        foreach ($block->getVorganger() as $data) {
            $block->removeVorganger($data);
        }
        $this->em->persist($block);
        $this->em->flush();

        $kinder = $this->childInBlockService->getCurrentChildAndFuturerChildOfZeitblock($block, new \DateTime());
        $transArray = array(
            '%day%' => $block->getWochentagString(),
            '%from%' => $block->getVon()->format('H:i'),
            '%to%' => $block->getBis()->format('H:i'),
            '%shool%' => $block->getSchule()->getName()
        );
        foreach ($kinder as $data) {
            $eltern = $this->elternService->getLatestElternFromChild($data);
            if ($eltern) {
                $this->anmeldeService->sendEmail($data, $eltern, $block->getSchule()->getStadt(), $this->translator->trans('Leider wurde das Betreuungszeitfenster am %day% von %from% bis %to% in der %shool% für folgendes Kind abgesagt:', $transArray, null, $eltern->getLanguage()));
                $this->anmeldeService->setBetreff($this->translator->trans('Absage des Betreuungszeitfensters: %day% von %from% bis %to% der Schule %shool%', $transArray, null, $eltern->getLanguage()));
                $this->anmeldeService->send($data, $eltern);
            }


        }

        return $this->translator->trans('Erfolgreich gelöscht');
    }

    public function restoreBlock(Zeitblock $block)
    {
        $block->setDeleted(false);

        $this->em->persist($block);
        $this->em->flush();

        $kinder = $this->childInBlockService->getCurrentChildAndFuturerChildOfZeitblock($block, new \DateTime());
        $transArray = array(
            '%day%' => $block->getWochentagString(),
            '%from%' => $block->getVon()->format('H:i'),
            '%to%' => $block->getBis()->format('H:i'),
            '%shool%' => $block->getSchule()->getName()
        );

        foreach ($kinder as $data) {
            $eltern = $this->elternService->getLatestElternFromChild($data);
            if ($eltern) {
                $this->anmeldeService->sendEmail($data, $eltern, $block->getSchule()->getStadt(), $this->translator->trans('Es wurde das Betreuungszeitfenster am %day% von %from% bis %to% in der %shool% für folgendes Kind wiederhergestellt:', $transArray, null, $eltern->getLanguage()));
                $this->anmeldeService->setBetreff($this->translator->trans('Wiederherstellung des Betreuungszeitfensters: %day% von %from% bis %to% der Schule %shool%', $transArray, null, $eltern->getLanguage()));
                $this->anmeldeService->send($data, $eltern);
            }

        }

        return $this->translator->trans('Erfolgreich wiederhergestellt');
    }
}
