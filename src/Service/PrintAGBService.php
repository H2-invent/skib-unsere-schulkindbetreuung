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
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use WhiteOctober\TCPDFBundle\Controller\TCPDFController;

class PrintAGBService
{
    private $mailer;
    private $templating;
    private  $translator;
    protected $parameterBag;
    private $pdf;
    public function __construct(TCPDFController $tcpdf,\Swift_Mailer $mailer, EngineInterface $templating,TranslatorInterface $translator,ParameterBagInterface $parameterBag)
    {
        $this->mailer =  $mailer;
        $this->templating = $templating;
        $this->translator = $translator;
        $this->parameterBag = $parameterBag;
        $this->pdf = $tcpdf;
    }

    public function printAGB($text, $type = 'D', ?Stadt $stadt=null, ?Organisation $organisation = null)
    {


        $pdf = $this->pdf->create('vertical', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->setFontSubsetting(true);

        $pdf->SetFont('freeserif', '', 10);
        $fileName = '';
        //$pdf-> = $this->container->get("white_october.tcpdf")->create('vertical', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        //todo hier musss der Test raus
        $pdf->SetAuthor($fileName);
        $pdf->SetTitle($fileName);
        $pdf->SetSubject($fileName);
        $pdf->SetMargins(20, 15, 20, true);
        $pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);
        $pdf->setHeaderData('', 0, '', '', array(0, 0, 0), array(255, 255, 255));
        $pdf->setFooterData(1, 1);

        //$pdf->SetMargins(20,20,40, true);
        $pdf->AddPage();

        $pdf->setJPEGQuality(75);
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);


        $logo = '';
        if($stadt){
            $pdf->setStadt($stadt);
            $fileName = 'AGB '.$stadt->getName();
        if ($stadt->getImage()) {

            $logo = $this->templating->render('pdf/img.html.twig', array('stadt' => $stadt));
            $logo = $this->parameterBag->get('kernel.project_dir').'/public'.$logo;
            $im = file_get_contents($logo);
            $imdata = base64_encode($im);
            $imgdata = base64_decode($imdata);
            $pdf->Image('@'.$imgdata,140,20,50);
        }
        }else{
            $fileName ='Datenschutz '.$organisation->getName();
            $pdf->setOrganisation($organisation);
            if ($organisation->getImage()) {
                $logo = $this->templating->render('pdf/img.html.twig', array('stadt' => $organisation));
                $logo = $this->parameterBag->get('kernel.project_dir').'/public'.$logo;
                $im = file_get_contents($logo);
                $imdata = base64_encode($im);
                $imgdata = base64_decode($imdata);
                $pdf->Image('@'.$imgdata,140,20,50);
            }
        }




        $table = $this->templating->render('pdf/agb.html.twig',array('text'=>$text));
        $pdf->writeHTMLCell(
            $w = 0,
            $h = 0,
            $x = 20,
            $y = 70,
            $table,
            $border = 0,
            $ln = 1,
            $fill = 0,
            $reseth = true,
            $align = '',
            $autopadding = true
        );

        // hier beginnt die Seite mit den Kindern


  return  $pdf->Output($fileName.".pdf", $type); // This will output the PDF as a Download
    }


}