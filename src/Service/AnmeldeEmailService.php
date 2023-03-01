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
use League\Flysystem\FilesystemOperator;
use Qipsius\TCPDFBundle\Controller\TCPDFController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;


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
    private FilesystemOperator $internFileSystem;

    public function __construct(FilesystemOperator $internFileSystem, ParameterBagInterface $parameterBag, PrintAGBService $printAGBService, PrintService $print, TCPDFController $tcpdf, TranslatorInterface $translator, IcsService $icsService, Environment $templating, MailerService $mailer)
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
        $this->internFileSystem = $internFileSystem;
    }

    public function sendEmail(Kind $kind, Stammdaten $adresse, Stadt $stadt, $text, $dontSendBeworben = false)
    {
        $this->attachment = array();
        $sessionLocale = $this->translator->getLocale();

        if (count($kind->getBeworben()->toArray()) == 0) {//Es gibt keine Zeitblöcke die nur beworben sind. Diese müssen erst noch genehmigt werden HIer werden  PDFs versandt
            $fileName = $kind->getVorname() . '_' . $kind->getNachname() . '_' . $kind->getSchule()->getName();
            $beruflicheSituation = (new LoerrachWorkflowController($this->translator))->beruflicheSituation;
            $pdf = $this->print->printAnmeldebestaetigung(
                $kind,
                $adresse,
                $stadt,
                $fileName,
                $beruflicheSituation,
                $kind->getSchule()->getOrganisation(),
                'S',
                $stadt->getSettingEncryptEmailAttachment()
            );
            $this->attachment[] = array('type' => 'application/pdf', 'filename' => $fileName . '.pdf', 'body' => $pdf);
            $this->attachment[] = array(
                'type' => 'application/pdf',
                'filename' => $this->translator->trans('Vertragsbedingungen ') . ' ' . $stadt->getSlug() . '.pdf',
                'body' => $this->abgService->printAGB(
                    $stadt->translate()->getAgb(),
                    'S',
                    $stadt,
                    null
                )
            );

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
            if ($stadt->getSettingsSkibDisableIcs() !== true){
                $this->attachment[] = array('type' => 'text/calendar', 'filename' => $kind->getVorname() . ' ' . $kind->getNachname() . '.ics', 'body' => $this->ics->toString());
            }

            foreach ($stadt->getEmailDokumenteSchulkindbetreuungBuchung() as $att) {
                $this->attachment[] = array(
                    'body' => $this->internFileSystem->read($att->getFileName()),
                    'filename' => $att->getOriginalName(),
                    'type' => $att->getType()
                );
            }

            if ($adresse->getLanguage()) {
                $this->translator->setLocale($adresse->getLanguage());
            }
            $this->betreff = $this->translator->trans('Buchungsbestätigung der Schulkindbetreuung für ') . $kind->getVorname() . ' ' . $kind->getNachname();
            $this->content = $this->templating->render('email/anmeldebestatigung.html.twig', array('eltern' => $adresse, 'kind' => $kind, 'stadt' => $stadt, 'text' => $text));
            $this->translator->setLocale($sessionLocale);

        } else {// es gibt noch beworbene Zeitblöcke
            if (!$dontSendBeworben) {
                foreach ($stadt->getEmailDokumenteSchulkindbetreuungAnmeldung() as $att) {
                    $this->attachment[] = array(
                        'body' => $this->internFileSystem->read($att->getFileName()),
                        'filename' => $att->getOriginalName(),
                        'type' => $att->getType()
                    );
                }
                if ($adresse->getLanguage()) {
                    $this->translator->setLocale($adresse->getLanguage());
                }
                $this->betreff = $this->translator->trans('Vorläufige Information über die Anmeldung zur Schulkindbetreuung für %vorname% %nachname%', array('%vorname%' => $kind->getVorname(), '%nachname%' => $kind->getNachname()));
                $this->content = $this->templating->render('email/anmeldebestatigungBeworben.html.twig', array('eltern' => $adresse, 'kind' => $kind, 'stadt' => $stadt));
                $this->translator->setLocale($sessionLocale);
            }
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

    public function send(Kind $kind, Stammdaten $adresse)
    {

        $this->mailer->sendEmail(
            $kind->getSchule()->getOrganisation()->getName(),
            $kind->getSchule()->getOrganisation()->getEmail(),
            $adresse->getEmail(),
            $this->betreff,
            $this->content,
            $kind->getSchule()->getOrganisation()->getEmail(),
            $this->attachment);
        foreach ($adresse->getPersonenberechtigters() as $data) {

            $this->mailer->sendEmail(
                $kind->getSchule()->getOrganisation()->getName(),
                $kind->getSchule()->getOrganisation()->getEmail(),
                $data->getEmail(),
                $this->betreff,
                $this->content,
                $kind->getSchule()->getOrganisation()->getEmail(),
                $this->attachment);
        }
    }
}
