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
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use WhiteOctober\TCPDFBundle\Controller\TCPDFController;

class AnmeldeEmailService
{
    private $print;
    private $tcpdf;
    private $translator;
    private $ics;
    private $templating;
    private $mailer;
    private $abgService;
    private $parameterbag;
    private $attachment;
    private $betreff;
    private $content;


    public function __construct(ParameterBagInterface $parameterBag, PrintAGBService $printAGBService, PrintService $print, TCPDFController $tcpdf, TranslatorInterface $translator, IcsService $icsService, EngineInterface $templating, MailerService $mailer)
    {
        $this->print = $print;
        $this->tcpdf = $tcpdf;
        $this->translator = $translator;
        $this->ics = $icsService;
        $this->templating = $templating;
        $this->mailer = $mailer;
        $this->abgService = $printAGBService;
        $this->parameterbag = $parameterBag;
        $this->attachment = null;
        $this->betreff = null;
        $this->content = null;
    }

    public function sendEmail(Kind $kind, Stammdaten $adresse, Stadt $stadt,$text)
    {
        $this->attachment = array();
        if (count($kind->getBeworben()->toArray()) == 0) {//Es gibt keine Zeitblöcke die nur beworben sind. Diese müssen erst noch genehmigt werden HIer werden  PDFs versandt
            $fileName = $kind->getVorname() . '_' . $kind->getNachname() . '_' . $kind->getSchule()->getName();
            $beruflicheSituation = (new LoerrachWorkflowController($this->translator))->beruflicheSituation;
            $pdf = $this->print->printAnmeldebestaetigung(
                $kind,
                $adresse,
                $stadt,
                $this->tcpdf,
                $fileName,
                $beruflicheSituation,
                $kind->getZeitblocks()[0]->getSchule()->getOrganisation(),
                'S'
            );
            $this->attachment[] = array('type' => 'application/pdf', 'filename' => $fileName . '.pdf', 'body' => $pdf);
            $this->attachment[] = array('type' => 'application/pdf', 'filename' => $this->translator->trans('Vertragsbedingungen ') . ' ' . $stadt->getSlug() . '.pdf', 'body' => $this->abgService->printAGB($stadt->translate()->getAgb(), 'S', $stadt, null));

            // here we build the ics to import into a calendar
            foreach ($kind->getZeitblocks() as $data2) {
                $startDate = $data2->getFirstDate()->format('Ymd');
                $this->ics->add(
                    array(
                        'location' => $data2->getSchule()->getOrganisation()->getName(),
                        'description' => $data2->getGanztag() == 0 ? $this->translator->trans('Mittagessen') : $this->translator->trans(
                            'Betreuung'),
                        'dtstart' => $startDate . 'T' . $data2->getVon()->format('His'),
                        'dtend' => $startDate . 'T' . $data2->getBis()->format('His'),
                        'summary' => $data2->getGanztag() == 0 ? $this->translator->trans('Mittagessen') : $this->translator->trans(
                            'Betreuung'),
                        'url' => '',
                        'rrule' => 'FREQ=WEEKLY;UNTIL=' . $data2->getActive()->getBis()->format('Ymd') . 'T000000'
                    )
                );
            }
            $this->attachment[] = array('type' => 'text/calendar', 'filename' => $kind->getVorname() . ' ' . $kind->getNachname() . '.ics', 'body' => $this->ics->toString());
            $this->content = $this->templating->render('email/anmeldebestatigung.html.twig', array('eltern' => $adresse, 'kind' => $kind, 'stadt' => $stadt,'text'=>$text));


        } else {// es gibt noch beworbene Zeitblöcke
            $this->content = $this->templating->render('email/anmeldebestatigungBeworben.html.twig', array('eltern' => $adresse, 'kind' => $kind, 'stadt' => $stadt));

        }
    }

    /**
     * @param null $betreff
     */
    public function setBetreff($betreff): void
    {
        $this->betreff = $betreff;
    }

    /**
     * @param null $content
     */
    public function setContent($content): void
    {
        $this->content = $content;
    }
    public function send(Kind $kind,Stammdaten $adresse){
        $this->mailer->sendEmail(
            $kind->getSchule()->getOrganisation()->getName(),
            $kind->getSchule()->getOrganisation()->getEmail(),
            $adresse->getEmail(),
            $this->betreff,
            $this->content,
            $kind->getSchule()->getOrganisation()->getEmail(),
            $this->attachment);
    }
}
