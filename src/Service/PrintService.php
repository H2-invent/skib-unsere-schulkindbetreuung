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
use League\Flysystem\FilesystemInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use WhiteOctober\TCPDFBundle\Controller\TCPDFController;

class PrintService
{

    private $templating;
    private $translator;
    protected $parameterBag;
    private $fileSystem;
    private $generator;

    public function __construct(UrlGeneratorInterface $urlGenerator, FilesystemInterface $publicUploadsFilesystem, \Swift_Mailer $mailer, EngineInterface $templating, TranslatorInterface $translator, ParameterBagInterface $parameterBag)
    {

        $this->templating = $templating;
        $this->translator = $translator;
        $this->parameterBag = $parameterBag;
        $this->fileSystem = $publicUploadsFilesystem;
        $this->generator = $urlGenerator;
    }

    public function printAnmeldebestaetigung(Kind $kind, Stammdaten $elter, Stadt $stadt, TCPDFController $tcpdf, $fileName, $beruflicheSituation, Organisation $organisation, $type = 'D')
    {
        $pdf = $tcpdf->create();
        $pdf->setOrganisation($organisation);
        $pdf = $this->preparePDF($pdf, 'Test', 'test', 'test', null, $organisation);

        $adressComp = '<p><small>' . $organisation->getName() . ' | ' . $organisation->getAdresse() . $organisation->getAdresszusatz() . ' | ' . $organisation->getPlz() . (' ') . $organisation->getOrt() . '</small><br><br>';

        $adressComp = $adressComp . $elter->getVorname() . ' ' . $elter->getName();
        $adressComp .= '<br>' . $elter->getStrasse();
        $adressComp = $adressComp . ($elter->getAdresszusatz() ? ('<br>' . $elter->getAdresszusatz()) : '');
        $adressComp .= '<br>' . $elter->getPlz() . ' ' . $elter->getStadt();

        $adressComp = $adressComp . '</p>';

        $pdf->writeHTMLCell(
            0,
            0,
            20,
            50,
            $adressComp,
            0,
            1,
            0,
            true,
            '',
            true
        );


        $kontaktDaten = '<table cellspacing="3px">' .

            '<tr>' . '<td align="right">' . $this->translator->trans('Sicherheitscode') . ': </td><td  align="left" >' . $elter->getSecCode() . '</td></tr>' .
            '<tr>' . '<td align="right">' . $this->translator->trans('Anmeldedatum') . ': </td><td  align="left" >' . $elter->getCreatedAt()->format('d.m.Y') . '</td></tr>' .
            '<tr>' . '<td align="right">' . $this->translator->trans('Betreuende Organisation') . ': </td><td  align="left" >' . $kind->getSchule()->getOrganisation()->getName() . '</td></tr>' .
            '<tr>' . '<td align="right">' . $this->translator->trans('Ansprechpartner') . ': </td><td  align="left" >' . $kind->getSchule()->getOrganisation()->getAnsprechpartner() . '</td></tr>' .
            '<tr>' . '<td align="right">' . $this->translator->trans('Telefonnummer') . ': </td><td  align="left" >' . $kind->getSchule()->getOrganisation()->getTelefon() . '</td></tr>';
        '<tr>' . '<td align="right">' . $this->translator->trans('E-Mail') . ': </td><td  align="left" >' . $kind->getSchule()->getOrganisation()->getEmail() . '</td></tr>';
        $kontaktDaten .= '</table>';
        $pdf->writeHTMLCell(
            300,
            0,
            10,
            65,
            $kontaktDaten,
            0,
            1,
            0,
            true,
            'L',
            true
        );
        $elternDaten = $this->templating->render('pdf/eltern.html.twig', array('kind' => $kind, 'eltern' => $elter, 'einkommen' => $stadt->getGehaltsklassen(), 'beruflicheSituation' => array_flip($beruflicheSituation)));
        $pdf->writeHTMLCell(
            0,
            0,
            20,
            100,
            $elternDaten,
            0,
            1,
            0,
            true,
            '',
            true
        );
        // hier beginnt die Seite mit den Kindern
        $pdf->AddPage('H', 'A4');
        $pdf = $this->addChildDetails($kind, $pdf);


        $pdf->AddPage('L', 'A4');
        $blocks = $kind->getRealZeitblocks()->toArray();
        $blocks = array_merge($blocks, $kind->getBeworben()->toArray());
        $render = array();
        foreach ($blocks as $data) {
            $render[$data->getWochentag()][] = $data;
        }

        $table = '';
        $t = 0;
        do {
            $table .= '<tr>';
            for ($i = 0; $i < 7; $i++) {
                $table .= '<td>';
                if (isset($render[$i])) {
                    $block = $render[$i][0];
                    $table .= '<p>' . ($block->getGanztag() == 0 ? $this->translator->trans('Mittagessen') : '') . '</p>';
                    $table .= '<p>' . (($block->getMin() || $block->getMax()) ? $this->translator->trans('Warten auf Bestätigung') : '') . '</p>';
                    $table .= $block->getVon()->format('H:i');
                    $table .= ' - ' . $block->getBis()->format('H:i');

                    \array_splice($render[$i], 0, 1);

                    if (count($render[$i]) == 0) {
                        unset($render[$i]);

                    }
                }
                $table .= '</td>';

            }

            $table .= '</tr>';

            if (count($render) == 0) {
                break;
            }
            $t++;
        } while ($t < 100);

        $kindData = $this->templating->render('pdf/kind.html.twig', array('kind' => $kind, 'table' => $table));
        $pdf->writeHTMLCell(
            0,
            0,
            20,
            20,
            $kindData,
            0,
            1,
            0,
            true,
            '',
            true
        );
        $pdf->AddPage('H    ', 'A4');
        $pdf = $this->addCard($kind, $pdf);
        return $pdf->Output($fileName . ".pdf", $type); // This will output the PDF as a Download
    }

    public function printChildDetail(Kind $kind, Stammdaten $elter, TCPDFController $tcpdf, $fileName, Organisation $organisation, $type = 'D')
    {
        $pdf = $tcpdf->create();
        $pdf->setOrganisation($organisation);
        $pdf = $this->preparePDF($pdf, 'Test', 'Kinder Details', 'test', null, $organisation);
        $pdf = $this->addChildDetails($kind, $pdf);
        $pdf->AddPage('H    ', 'A4');
        $pdf = $this->addEltern($elter, $pdf);
        $pdf->AddPage('H    ', 'A4');
        $pdf = $this->addCard($kind, $pdf);
        return $pdf->Output($fileName . ".pdf", $type); // This will output the PDF as a Download
    }

    private function generateChildPage(Kind $kind)
    {
        $zeitBloeckeGebucht = array();
        foreach ($kind->getRealZeitblocks() as $data) {
            $zeitBloeckeGebucht[$data->getWochentag()][] = $data;
        }
        $zeitBloeckeAngemeldet = array();
        foreach ($kind->getBeworben() as $data) {
            $zeitBloeckeAngemeldet[$data->getWochentag()][] = $data;
        }

        return $this->templating->render('pdf/kindOrganisation.html.twig', array('k' => $kind, 'beworben' => $zeitBloeckeAngemeldet, 'zeitblock' => $zeitBloeckeGebucht));

    }


    public function printChildList($kinder, Organisation $organisation, $text, $fileName, TCPDFController $tcpdf, $type = 'I')
    {

        $pdf = $tcpdf->create();
        $pdf->setOrganisation($organisation);
        $pdf = $this->preparePDF($pdf, 'Kinder in Organistaion ' . $organisation->getName(), 'test', 'test', null, $organisation);


        $kindData = $this->templating->render('pdf/kinderliste.html.twig', array('text' => $text, 'kinder' => $kinder));
        $pdf->writeHTMLCell(
            0,
            0,
            20,
            50,
            $kindData,
            0,
            1,
            0,
            true,
            '',
            true
        );


        return $pdf->Output($fileName . ".pdf", $type); // This will output the PDF as a Download
    }

    public function preparePDF($pdf, $title, $author, $subject, ?Stadt $stadt, ?Organisation $organisation)
    {

        $pdf->SetAuthor($author);
        $pdf->SetTitle($title);
        $pdf->SetSubject($subject);
        $pdf->setFontSubsetting(true);
        $pdf->SetFont('helvetica', '', 10, '', true);
        $pdf->SetMargins(20, 15, 20, true);
        $pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);
        $pdf->setHeaderData('', 0, '', '', array(0, 0, 0), array(255, 255, 255));
        $pdf->setFooterData(1, 1);
        $pdf->AddPage();
        $pdf->setJPEGQuality(75);
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        if ($organisation) {
            if ($organisation->getImage()) {
                $im = $this->fileSystem->read($organisation->getImage());
                $imdata = base64_encode($im);
                $imgdata = base64_decode($imdata);
                $pdf->Image('@' . $imgdata, 140, 20, 0, 30);
            }
        }
        if ($stadt) {
            if ($stadt->getImage()) {
                $im = $this->fileSystem->read($stadt->getImage());
                $imdata = base64_encode($im);
                $imgdata = base64_decode($imdata);
                $pdf->Image('@' . $imgdata, 140, 20, 0, 30);
            }
        }
        return $pdf;
    }


    public function addEltern(Stammdaten $eltern, \TCPDF $pdf)
    {

        $elternDaten = $this->templating->render('pdf/elternOrganisation.html.twig', array('eltern' => $eltern));
        $pdf->writeHTMLCell(
            0,
            0,
            20,
            30,
            $elternDaten,
            0,
            1,
            0,
            true,
            '',
            true
        );
        return $pdf;
    }

    public function addChildDetails(Kind $kind, \TCPDF $pdf)
    {

        $pdf->writeHTMLCell(
            0,
            0,
            20,
            50,
            $this->generateChildPage($kind),
            0,
            1,
            0,
            true,
            '',
            true
        );
        return $pdf;
    }


    public function addCard(Kind $kind, \TCPDF $pdf)
    {
        //die seite mit der kleinen checkin card

        $pdf->writeHTMLCell(
            0,
            0,
            20,
            30,
            $this->templating->render('pdf/checkInAusweis.html.twig', array('kind' => $kind)),
            $border = 0,
            1,
            0,
            true,
            '',
            true
        );
        $style = array(
            'border' => false,
            'padding' => 0,
            'fgcolor' => array(0, 0, 0),
            'bgcolor' => false
        );

// QRCODE,H : QR-CODE Best error correction
        $pdf->write2DBarcode($this->generator->generate('checkin_schulkindbetreuung', array('kindID' => $kind->getId()), UrlGeneratorInterface::ABSOLUTE_URL), 'QRCODE,H', 25, 68, 30, 30, $style, 'N');
        return $pdf;
    }
}
