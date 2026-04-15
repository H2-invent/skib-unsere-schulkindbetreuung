<?php
/**
 * Created by PhpStorm.
 * User: Emanuel
 * Date: 03.10.2019
 * Time: 19:01
 */

namespace App\Service;


use App\Entity\KindFerienblock;
use Qipsius\TCPDFBundle\Controller\TCPDFController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;


class FerienPrintService
{

    protected $parameterBag;
    public function __construct(private TCPDFController $tcpdfController, private Environment $templating, ParameterBagInterface $parameterBag, private UrlGeneratorInterface $router)

    {

        $this->parameterBag = $parameterBag;

    }

    public function printPdfTicket( $fileName,  KindFerienblock $ferienblock, $type = 'D')
    {
        $kind = $ferienblock->getKind();
        $organisation = $ferienblock->getFerienblock()->getOrganisation();
        $pdf = $this->tcpdfController->create();
        $pdf->setOrganisation($organisation);
        $pdf = $this->preparePDF($pdf,'Ticket','H2 invent','Ticket für Ferienprogram');


        // set style for barcode
        $style = array(
            'border' => false,
            'vpadding' => 'auto',
            'hpadding' => 'auto',
            'fgcolor' => array(0,0,0),
            'bgcolor' => false, //array(255,255,255)
            'module_width' => 1, // width of a single module in points
            'module_height' => 1 // height of a single module in points
        );

        $code = $this->router->generate('ferien_checkin', array('checkinID' =>$ferienblock->getCheckinID()),UrlGeneratorInterface::ABSOLUTE_URL);
        $pdf->write2DBarcode($code, 'QRCODE,Q', 139, 39, 45, 45, $style, 'N');

        $kindData = $this->templating->render('ferien_ticket/index.html.twig', array('kind' => $kind, 'organisation'=>$organisation, 'ferienblock'=>$ferienblock));
        $pdf->writeHTMLCell(
            0,
            0,
            20,
            15,
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


    public function preparePDF($pdf, $title, $author, $subject)
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
        return $pdf;
    }
}
