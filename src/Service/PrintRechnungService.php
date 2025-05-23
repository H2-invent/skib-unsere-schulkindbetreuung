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
use App\Entity\Rechnung;
use App\Entity\Stadt;
use App\Entity\Stammdaten;


use League\Flysystem\FilesystemOperator;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

use Qipsius\TCPDFBundle\Controller\TCPDFController;

class PrintRechnungService
{


    private $translator;
    protected $parameterBag;
    private $pdf;
    private $fileSystem;

    public function __construct(FilesystemOperator $publicUploadsFilesystem, TCPDFController $tcpdf,  TranslatorInterface $translator, ParameterBagInterface $parameterBag)
    {


        $this->translator = $translator;
        $this->parameterBag = $parameterBag;
        $this->pdf = $tcpdf;
        $this->fileSystem = $publicUploadsFilesystem;
    }

    public function printRechnung($fileName, Organisation $organisation, Rechnung $rechnung, $type = 'D')
    {

        $eltern = $rechnung->getStammdaten();
        $pdf = $this->pdf;
        $pdf = $this->pdf->create();
        $pdf->setOrganisation($organisation);
        $fileName = $organisation->getName() . '_' . $rechnung->getRechnungsnummer();

        $pdf->SetAuthor($fileName);
        $pdf->SetTitle($fileName);
        $pdf->SetSubject($fileName);
        $pdf->setFontSubsetting(true);
        $pdf->SetFont('helvetica', '', 10, '', true);
        $pdf->SetMargins(20, 15, 20, true);
        $pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);
        $pdf->setHeaderData('', 0, '', '', array(0, 0, 0), array(255, 255, 255));
        $pdf->setFooterData(1, 1);


        $pdf->AddPage();
        $adressComp = '<p><small>' . $organisation->getName() . ' | ' . $organisation->getAdresse() . $organisation->getAdresszusatz() . ' | ' . $organisation->getPlz() . (' ') . $organisation->getOrt() . '</small><br><br>';

        $adressComp = $adressComp . $eltern->getVorname() . ' ' . $eltern->getName();
        $adressComp .= '<br>' . $eltern->getStrasse();
        $adressComp = $adressComp . ($eltern->getAdresszusatz() ? ('<br>' . $eltern->getAdresszusatz()) : '');
        $adressComp .= '<br>' . $eltern->getPlz() . ' ' . $eltern->getStadt();

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

        $pdf->setJPEGQuality(75);
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);



        if ($organisation->getImage()) {
           $im = $this->fileSystem->read($organisation->getImage());
            $imdata = base64_encode($im);
            $imgdata = base64_decode($imdata);
            $pdf->Image('@' . $imgdata, 140, 20, 0,30);
        }
        $kontaktDaten = '<table cellspacing="3px">' .

            '<tr>' . '<td align="right">' . $this->translator->trans('Rechnungsdatum') . ': </td><td  align="left" >' . $rechnung->getCreatedAt()->format('d.m.Y') . '</td></tr>' .
            '<tr>' . '<td align="right">' . $this->translator->trans('Rechnungsnummer') . ': </td><td  align="left" >' . $rechnung->getRechnungsnummer() . '</td></tr>' .

            '<tr>' . '<td align="right">' . $this->translator->trans('Betreuende Organisation') . ': </td><td  align="left" >' . $organisation->getName() . '</td></tr>' .
            '<tr>' . '<td align="right">' . $this->translator->trans('Ansprechpartner') . ': </td><td  align="left" >' . $organisation->getAnsprechpartner() . '</td></tr>' .
            '<tr>' . '<td align="right">' . $this->translator->trans('Telefonnummer') . ': </td><td  align="left" >' . $organisation->getTelefon() . '</td></tr>';
        '<tr>' . '<td align="right">' . $this->translator->trans('E-Mail') . ': </td><td  align="left" >' . $organisation->getEmail() . '</td></tr>';
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

        $pdf->writeHTMLCell(
            0,
            0,
            20,
            100,
            $rechnung->getPdf(),
            0,
            1,
            0,
            true,
            '',
            true
        );

        // hier beginnt die Seite mit den Kindern


        return $pdf->Output($fileName . ".pdf", $type); // This will output the PDF as a Download
    }


}
