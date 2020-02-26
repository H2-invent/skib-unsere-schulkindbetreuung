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

use Doctrine\Common\Collections\ArrayCollection;
use League\Flysystem\FilesystemInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use WhiteOctober\TCPDFBundle\Controller\TCPDFController;

class PrintAGBService
{

    private $templating;

    protected $parameterBag;
    private $pdf;
    private $fileSystem;

    public function __construct(FilesystemInterface $publicUploadsFilesystem,TCPDFController $tcpdf, EngineInterface $templating,ParameterBagInterface $parameterBag)
    {

        $this->templating = $templating;

        $this->parameterBag = $parameterBag;
        $this->pdf = $tcpdf;
        $this->fileSystem = $publicUploadsFilesystem;
    }

    public function printAGB($text, $type = 'D', ?Stadt $stadt=null, ?Organisation $organisation = null)
    {


        $pdf = $this->pdf->create('vertical', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->setFontSubsetting(true);

        $pdf->SetFont('freeserif', '', 10);
        $fileName = '';

        $pdf->SetAuthor($fileName);
        $pdf->SetTitle($fileName);
        $pdf->SetSubject($fileName);
        $pdf->SetMargins(20, 15, 20, true);
        $pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);
        $pdf->setHeaderData('', 0, '', '', array(0, 0, 0), array(255, 255, 255));
        $pdf->setFooterData(1, 1);


        $pdf->AddPage();

        $pdf->setJPEGQuality(75);
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);



        if($stadt){
            $pdf->setStadt($stadt);
            $fileName = 'Vertragsbedingungen '.$stadt->getSlug();
            if ($stadt->getImage()) {
                $im = $this->fileSystem->read($stadt->getImage());
                $imdata = base64_encode($im);
                $imgdata = base64_decode($imdata);
                $pdf->Image('@' . $imgdata, 140, 20, 50);
            }

        }else{
            $fileName ='DSGVO '.$organisation->getName();
            $pdf->setOrganisation($organisation);
            if ($organisation->getImage()) {
                $im = $this->fileSystem->read($organisation->getImage());
                $imdata = base64_encode($im);
                $imgdata = base64_decode($imdata);
                $pdf->Image('@' . $imgdata, 140, 20, 50);
            }
        }




        $table = $this->templating->render('pdf/agb.html.twig',array('text'=>$text));
        $pdf->writeHTMLCell(
            0,
            0,
           20,
             70,
            $table,
             0,
             1,
            0,
            true,
            '',
             true
        );

        // hier beginnt die Seite mit den Kindern


  return  $pdf->Output($fileName.".pdf", $type); // This will output the PDF as a Download
    }


}
