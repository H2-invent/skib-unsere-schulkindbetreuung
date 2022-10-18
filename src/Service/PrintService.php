<?php
/**
 * Created by PhpStorm.
 * User: Emanuel
 * Date: 03.10.2019
 * Time: 19:01
 */

namespace App\Service;


use App\Entity\Active;
use App\Entity\Kind;
use App\Entity\Organisation;
use App\Entity\Schule;
use App\Entity\Stadt;
use App\Entity\Stammdaten;
use App\Entity\Zeitblock;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;
use Qipsius\TCPDFBundle\Controller\TCPDFController;

class PrintService
{

    private $templating;
    private $translator;
    protected $parameterBag;
    private $fileSystem;
    private $generator;
    private $em;

    public function __construct(EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator, FilesystemOperator $publicUploadsFilesystem, Environment $templating, TranslatorInterface $translator, ParameterBagInterface $parameterBag)
    {

        $this->templating = $templating;
        $this->translator = $translator;
        $this->parameterBag = $parameterBag;
        $this->fileSystem = $publicUploadsFilesystem;
        $this->generator = $urlGenerator;
        $this->em = $entityManager;
    }

    public function printAnmeldebestaetigung(Kind $kind, Stammdaten $elter, Stadt $stadt, TCPDFController $tcpdf, $fileName, $beruflicheSituation, Organisation $organisation, $type = 'D', $encyption = false)
    {
        $pdf = $tcpdf->create();

        $pdf->setOrganisation($organisation);
        $pdf = $this->preparePDF($pdf, $this->translator->trans('Anmeldebestätigung für die Schulkindbetreuung'), $organisation->getName(), $this->translator->trans('Anmeldebestätigung für die Schulkindbetreuung'), null, $organisation);
        if ($encyption) {
            $pdf->setProtection(array('modify'), $kind->getGeburtstag()->format('d.m.Y'), 'h2inventSkibIsTheBest', 3);
        }
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
        $table = $this->generateTimeTable($blocks);
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
        if ($stadt->getOnlineCheckinEnable()) {
            $pdf->AddPage('H    ', 'A4');
            $pdf = $this->addCard($kind, $pdf);
        }

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
        if ($kind->getSchule()->getOrganisation()->getStadt()->getOnlineCheckinEnable()) {
            $pdf->AddPage('H    ', 'A4');
            $pdf = $this->addCard($kind, $pdf);
        }

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


    public function printChildList($kinder, Organisation $organisation, $text, $fileName, TCPDFController $tcpdf, $wochentag = [0,1,2,3,4], $type = 'I')
    {

        $pdf = $tcpdf->create();
        $pdf->setOrganisation($organisation);
        $subject = 'Kinder in Organistaion ' . $organisation->getName();
        $pdf = $this->preparePDF($pdf, $subject, 'test', 'test', null, $organisation);
        $pdf->writeHTMLCell(0,
            0,
            20,
            100,
        '<h1>'.$subject.'</h1>',
            0,
            1,
            0,
            true,
            '',
            true
        );
        $pdf->AddPage('L');
        $kindData = $this->templating->render('pdf/kinderliste.html.twig', array('text' => $text, 'kinder' => $kinder, 'wochentag'=>$wochentag));
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

    public function preparePDF(\TCPDF $pdf, $title, $author, $subject, ?Stadt $stadt, ?Organisation $organisation)
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
        $w = 60;
        $h = 0;
        $imgdata = null;
        if ($organisation) {
            if ($organisation->getImage()) {
                $im = $this->fileSystem->read($organisation->getImage());
                $imdata = base64_encode($im);
                $imgdata = base64_decode($imdata);

                $dimensions = getimagesizefromstring($im);
                if ($dimensions[0] < $dimensions[1]) {
                    $h = 30;
                    $w = 0;
                }
            }
        }

        if ($stadt) {
            if ($stadt->getLogoStadt()) {
                $im = $this->fileSystem->read($stadt->getLogoStadt());
                $imdata = base64_encode($im);
                $imgdata = base64_decode($imdata);
                $dimensions = getimagesizefromstring($im);

                if ($dimensions[0] < $dimensions[1]) {
                    $h = 30;
                    $w = 0;
                }
            }

        }


        if ($imgdata) {
            $pdf->Image('@' . $imgdata, 140, 20, $w, $h);
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
            0,
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
        $pdf->write2DBarcode($this->generator->generate('checkin_schulkindbetreuung', array('kindID' => $kind->getId()), UrlGeneratorInterface::ABSOLUTE_URL), 'QRCODE,H', 25, 65, 31, 31, $style, 'N');

        return $pdf;
    }

    function printAnmeldeformular(Schule $schule, TCPDFController $tcpdf, $fileName, $beruflicheSituation, $gehaltsklassen, $cat, $type = 'D')
    {
        $catArr = array(1 => $this->translator->trans('Ganztag'), 2 => $this->translator->trans('Halbtag'));
        $pdf = $tcpdf->create();
        $organisation = $schule->getOrganisation();
        $pdf->setOrganisation($schule->getOrganisation());
        $pdf = $this->preparePDF($pdf, $this->translator->trans('Änderungsformular für die Schulkindbetreuung'), $organisation->getName(), $this->translator->trans('Änderungsformular für die Schulkindbetreuung'), null, null);

        if ($schule->getOrganisation()->getImage()) {
            $im = $this->fileSystem->read($schule->getOrganisation()->getImage());
            $imdata = base64_encode($im);
            $imgdata = base64_decode($imdata);
            $pdf->Image('@' . $imgdata, 25, 30, 50, 0);
        }


        if ($schule->getStadt()->getLogoStadt()) {
            $im = $this->fileSystem->read($schule->getStadt()->getLogoStadt());
            $imdata = base64_encode($im);
            $imgdata = base64_decode($imdata);
            $pdf->Image('@' . $imgdata, 100, 30, 50, 0);
        }

        $title = '<h1 style="text-align: center; font-size: 25px">Änderung der Anmeldung für die Schulkindbetreuung an der ' . $schule->getName() . '</h1>';

        $pdf->writeHTMLCell(
            170,
            0,
            20,
            100,
            $title,
            0,
            1,
            0,
            true,
            'C',
            true
        );


        if ($schule->getImage()) {
            $im = $this->fileSystem->read($schule->getImage());
            $imdata = base64_encode($im);
            $imgdata = base64_decode($imdata);
            $pdf->Image('@' . $imgdata, 30, 130, 150, 0);
        } else {
            $pdf->writeHTMLCell(
                170,
                0,
                20,
                150,
                '<h1 style="font-size: 45px">' . $schule->getName() . '</h1>',
                0,
                1,
                0,
                true,
                'C',
                true
            );
        }
        $text = '<div> <p style="font-size: 12px; color: red"><b>Bei nachträglichen Änderungen in der Anmeldung müssen mindestens die roten Felder ausgefüllt sein.</b></p></div>';

        $pdf->writeHTMLCell(
            170,
            0,
            20,
            230,
            $text,
            0,
            1,
            0,
            true,
            'C',
            true
        );
        // hier die Kinderdaten
        $blocks = array();
        $block['type'] = $catArr[$cat];
        $schulJahr = $this->em->getRepository(Active::class)->findActiveSchuljahrFromCity($schule->getOrganisation()->getStadt());
        $blockTmp = $this->em->getRepository(Zeitblock::class)->findBy(array('active'=>$schulJahr,'schule'=>$schule));
        foreach ($blockTmp as $data) {
            if ($data->getGanztag() == $cat && !$data->getDeleted() && !$data->getDeaktiviert()) {
                $block['data'][] = $data;
            }
        }
        $block['table'] = $this->generateTimeTable($block['data'], true);
        $block['data'] = $this->getBlocks($block['data']);

        $pdf->AddPage();
        $pdf->writeHTMLCell(
            0,
            0,
            20,
            30,
            $this->templating->render('download_formular/__kinder.html.twig', ['schule' => $schule, 'block' => $block]),
            0,
            1,
            0,
            true,
            '',
            true
        );

        // hier die ELterndaten
        $pdf->AddPage();
        $pdf->writeHTMLCell(
            0,
            0,
            20,
            30,
            $this->templating->render('download_formular/__elter.html.twig', array(
                'gehaltsklassen' => $gehaltsklassen,
                'beruflicheSitutuation' => $beruflicheSituation,
                'organisation' => $organisation,
                'stadt' => $organisation->getStadt())),
            0,
            1,
            0,
            true,
            '',
            true
        );


        return $pdf->Output($fileName . ".pdf", $type); // This will output the PDF as a Download

    }


    function generateTimeTable($blocks, $cross = false)
    {
        foreach ($blocks as $data) {
            $render[$data->getWochentag()][] = $data;
        }


        $table = '';
        $t = 0;
        do {
            $table .= '<tr>';
            for ($i = 0; $i < 7; $i++) {
                $table .= '<td align="center" valign="middle" style="border: 1px solid black">';
                if (isset($render[$i])) {

                    $block = $render[$i][0];
                    if ($block->getGanztag() == 0) {
                        $table .= '<p>' . $this->translator->trans('Mittagessen') . '</p>';

                    }
                    if (($block->getMin() || $block->getMax())) {
                        $table .= '<p>' . $this->translator->trans('Warten auf Bestätigung') . '</p>';
                    }
                    $table .= $block->getVon()->format('H:i') . ' - ' . $block->getBis()->format('H:i');
                    if ($cross) {
                        $table .= '<br><div><table width="100%" style="border:none"><tr style="border: none"><td style="border: 1px solid black; width:18px"></td><td style="border: none">' . $this->translator->trans('buchen') . '</td></tr></table></div>';
                    }
                    array_splice($render[$i], 0, 1);

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
        return $table;
    }

    function getBlocks($blocks)
    {
        foreach ($blocks as $data) {
            $render[$data->getWochentag()][] = $data;
        }
        return $render;
    }
}
