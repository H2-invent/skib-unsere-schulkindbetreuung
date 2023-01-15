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
use App\Entity\Organisation;
use App\Entity\Stadt;
use App\Entity\Stammdaten;
use League\Flysystem\FilesystemOperator;
use Qipsius\TCPDFBundle\Controller\TCPDFController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;


class StammdatenEditEmailService
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

    public function sendEmail(Stammdaten $adresse, Organisation $organisation, $text)
    {
        $this->attachment = array();
        $sessionLocale = $this->translator->getLocale();

        $pdf = $this->print->printElternDetail($adresse, $organisation);
        $this->attachment[] = array('type' => 'application/pdf', 'filename' => $adresse->getVorname() . ' ' . $adresse->getName() . '.pdf', 'body' => $pdf);
        if ($adresse->getLanguage()) {
            $this->translator->setLocale($adresse->getLanguage());
        }


        if ($adresse->getLanguage()) {
            $this->translator->setLocale($adresse->getLanguage());
        }
        $this->betreff = $this->translator->trans('Änderung der Stammdaten');
        $this->content = $this->templating->render('email/stammdatenEdit.html.twig', array('stammdaten' => $adresse, 'stadt' => $organisation->getStadt(),'organisation'=>$organisation));
        $this->translator->setLocale($sessionLocale);

    }

    /**
     * @param null $betreff
     */
    public
    function setBetreff($betreff): void
    {
        $this->betreff = $betreff;
    }

    /**
     * @param null $content
     */
    public
    function setContent($content): void
    {
        $this->content = $content;
    }

    public
    function send(Stammdaten $adresse,Organisation $organisation)
    {
        $this->mailer->sendEmail(
            $organisation->getName(),
            $organisation->getEmail(),
            $adresse->getEmail(),
            $this->betreff,
            $this->content,
            $organisation->getEmail(),
            $this->attachment);
        foreach ($adresse->getPersonenberechtigters() as $data) {
            $this->mailer->sendEmail(
                $organisation->getName(),
                $organisation->getEmail(),
                $adresse->getEmail(),
                $this->betreff,
                $this->content,
                $organisation->getEmail(),
                $this->attachment
            );
        }
    }
}
