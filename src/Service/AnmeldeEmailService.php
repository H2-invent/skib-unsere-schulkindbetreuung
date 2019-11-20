<?php
/**
 * Created by PhpStorm.
 * User: Emanuel
 * Date: 03.10.2019
 * Time: 19:01
 */

namespace App\Service;


use App\Entity\Kind;
use App\Entity\Organisation;
use App\Entity\Stadt;
use App\Entity\Stammdaten;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
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
    public function __construct(PrintAGBService $printAGBService,PrintService $print,TCPDFController $tcpdf,TranslatorInterface $translator,IcsService $icsService,EngineInterface $templating, MailerService $mailer)
    {
        $this->print = $print;
        $this->tcpdf = $tcpdf;
        $this->translator = $translator;
        $this->ics = $icsService;
        $this->templating = $templating;
        $this->mailer = $mailer;
        $this->abgService = $printAGBService;
    }

    public  function sendEmail(Kind $kind,Stammdaten $adresse,Stadt $stadt,$einkommensgruppen){
        $attachment = array();
        if (sizeof($kind->getBeworben()->toArray()) == 0) {//Es gibt keine Zeitblöcke die nur beworben sind. Diese müssen erst noch genehmigt werden HIer werden  PDFs versandt
            $fileName = $kind->getVorname() . '_' . $kind->getNachname() . '_' . $kind->getSchule()->getName() . '.pdf';

            $pdf = $this->print->printAnmeldebestätigung(
                $kind,
                $adresse,
                $stadt,
                $this->tcpdf,
                $fileName,
                $einkommensgruppen,
                $kind->getZeitblocks()[0]->getSchule()->getOrganisation(),
                'S'
            );
            $attachment[] = array('type' => 'application/pdf', 'filename' => $fileName . '.pdf', 'body' => $pdf);
            $attachment[] = array('type' => 'application/pdf', 'filename' => $this->translator->trans('AGB ') . ' ' . $stadt->getName() . '.pdf', 'body' => $this->abgService->printAGB($stadt->translate()->getAgb(), 'S', $stadt, null));


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
            $attachment[] = array('type' => 'text/calendar', 'filename' => $kind->getVorname() . ' ' . $kind->getNachname() . '.ics', 'body' => $this->ics->to_string());

            $icsService = new IcsService();
            $mailBetreff = $this->translator->trans('Buchungsbestätigung der Schulkindbetreuung für ') . $kind->getVorname() . ' ' . $kind->getNachname();
            $mailContent = $this->templating->render('email/anmeldebestatigung.html.twig', array('eltern' => $adresse, 'kind' => $kind, 'stadt' => $stadt));
            $this->mailer->sendEmail($kind->getSchule()->getOrganisation()->getName(), $kind->getSchule()->getOrganisation()->getEmail(), $adresse->getEmail(), $mailBetreff, $mailContent, $attachment);

        } else {// es gibt noch beworbene Zeitblöcke
            $mailBetreff = $this->translator->trans('Anmeldeinformation der Schulkindbetreuung für ') . $kind->getVorname() . ' ' . $kind->getNachname();
            $mailContent = $this->templating->render('email/anmeldebestatigungBeworben.html.twig', array('eltern' => $adresse, 'kind' => $kind, 'stadt' => $stadt));
            $this->mailer->sendEmail($kind->getSchule()->getOrganisation()->getName(), $kind->getSchule()->getOrganisation()->getEmail(), $adresse->getEmail(), $mailBetreff, $mailContent, $attachment);

        }
    }
}
