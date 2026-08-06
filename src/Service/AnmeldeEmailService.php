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
use App\Service\Gebuehrenbescheid\FeeSummaryBuilder;
use App\Service\Gebuehrenbescheid\PrintGebuehrenbescheidService;
use League\Flysystem\FilesystemOperator;
use Psr\Log\LoggerInterface;
use Qipsius\TCPDFBundle\Controller\TCPDFController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
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

    public function __construct(
        FilesystemOperator                             $internFileSystem,
        ParameterBagInterface                          $parameterBag,
        PrintAGBService                                $printAGBService,
        PrintService                                   $print,
        TCPDFController                                $tcpdf,
        TranslatorInterface                            $translator,
        IcsService                                     $icsService,
        Environment                                    $templating,
        MailerService                                  $mailer,
        private LoerrachWorkflowController             $loerrachWorkflowController,
        private RouterInterface                        $router,
        private readonly PrintGebuehrenbescheidService $gebuehrenbescheidService,
        private readonly FeeSummaryBuilder             $feeSummaryBuilder,
        private readonly LoggerInterface               $logger)
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

    /**
     * @return null
     */
    public function getBetreff()
    {
        return $this->betreff;
    }


    public function sendEmail(Kind $kind, Stammdaten $adresse, Stadt $stadt, $text, $dontSendBeworben = false, bool $withGebuehrenbescheid = true)
    {
        $this->attachment = array();
        $sessionLocale = $this->translator->getLocale();

        if (count($kind->getBeworben()->toArray()) == 0) {//Es gibt keine Zeitblöcke die nur beworben sind. Diese müssen erst noch genehmigt werden HIer werden  PDFs versandt
            $fileName = $kind->getVorname() . '_' .  $kind->getSchule()->getName();
            $beruflicheSituation = $this->loerrachWorkflowController->beruflicheSituation;
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
            $this->ics->clearAllAppointments();
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
            if ($stadt->getSettingsSkibDisableIcs() !== true) {
                $this->attachment[] = array('type' => 'text/calendar', 'filename' => $kind->getVorname() . '.ics', 'body' => $this->ics->toString());
            }

            foreach ($stadt->getEmailDokumenteSchulkindbetreuungBuchung() as $att) {
                $this->attachment[] = array(
                    'body' => $this->internFileSystem->read($att->getFileName()),
                    'filename' => $att->getOriginalName(),
                    'type' => $att->getType()
                );
            }

            if ($withGebuehrenbescheid) {
                $this->appendGebuehrenbescheid($kind, $adresse, $stadt);
            }

            if ($adresse->getLanguage()) {
                $this->translator->setLocale($adresse->getLanguage());
            }
            $this->betreff = $this->translator->trans('Buchungsbestätigung der Schulkindbetreuung für ') . $kind->getVorname() ;
            $this->content = $this->templating->render('email/anmeldebestatigung.html.twig', array(
                'eltern' => $adresse,
                'kind' => $kind,
                'stadt' => $stadt,
                'text' => $text,
                'parentSickRequestUrl' => $this->getParentSickRequestUrl($stadt),
            ));
            $this->translator->setLocale($sessionLocale);
            return true;
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
                $this->betreff = $this->translator->trans('Vorläufige Information über die Anmeldung zur Schulkindbetreuung für %vorname%', array( '%vorname%'=>$kind->getVorname(), '%nachname%' => $kind->getNachname()));
                $blocks = $kind->getBeworben()->toArray();
                usort($blocks, function (Zeitblock $a, Zeitblock $b) {
                    return $a->getVon() <=> $b->getVon();
                });

                $this->content = $this->templating->render('email/anmeldebestatigungBeworben.html.twig', array(
                    'eltern' => $adresse,
                    'kind' => $kind,
                    'stadt' => $stadt,
                    'blocks' => $blocks,
                    'parentSickRequestUrl' => $this->getParentSickRequestUrl($stadt),
                ));
                $this->translator->setLocale($sessionLocale);
                return true;
            }
        }
        return false;
    }

    private function getParentSickRequestUrl(Stadt $stadt): ?string
    {
        if (!$stadt->isParentSickReportsEnabled()) {
            return null;
        }

        return $this->router->generate(
            'parent_sick_request',
            ['stadtSlug' => $stadt->getSlug()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
    }

    /**
     * Attaches the Gebührenbescheid PDF if the city switched it on.
     *
     * The toggle alone decides. A city that has not authored a template gets the built-in default layout from
     * templates/pdf/gebuehrenbescheid.html.twig, which is also what the admin preview shows.
     *
     * Only ever called from the branch where the child has no beworben blocks left, because a fee notice must
     * not bill blocks that are still only applied for.
     */
    private function appendGebuehrenbescheid(Kind $kind, Stammdaten $adresse, Stadt $stadt): void
    {
        if ($stadt->getSettingsSkibSendGebuehrenbescheid() !== true) {
            return;
        }

        // Resolved once and passed down explicitly, so this does not depend on the translator locale being
        // switched further down in sendEmail().
        $locale = $adresse->getLanguage() ?: $this->parameterbag->get('kernel.default_locale');

        if (!$this->gebuehrenbescheidService->hasTemplate($stadt, $locale)) {
            $this->logger->info('Gebuehrenbescheid: no city template, using the built-in default layout', [
                'stadt' => $stadt->getId(),
                'locale' => $locale,
                'kind' => $kind->getId(),
            ]);
        }

        try {
            $fileName = $this->translator->trans('Gebuehrenbescheid') . '_' . $kind->getVorname();

            $this->attachment[] = array(
                'type' => 'application/pdf',
                'filename' => $fileName . '.pdf',
                'body' => $this->gebuehrenbescheidService->render(
                    $stadt,
                    $kind,
                    $adresse,
                    $kind->getSchule()?->getOrganisation(),
                    $this->feeSummaryBuilder->build($kind),
                    $locale,
                    $fileName,
                ),
            );
        } catch (\Throwable $exception) {
            // The template is city-authored Twig and the fee total runs through eval(), so a mistake in either
            // must not stop the confirmation mail from going out.
            $this->logger->error('Gebuehrenbescheid could not be rendered', [
                'stadt' => $stadt->getId(),
                'kind' => $kind->getId(),
                'exception' => $exception,
            ]);
        }
    }

    /**
     * @return array<int, array{type: string, filename: string, body: string}>
     */
    public function getAttachments(): array
    {
        return $this->attachment ?? [];
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
        return $this->betreff;
    }
}
