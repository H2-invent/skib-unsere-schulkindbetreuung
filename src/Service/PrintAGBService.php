<?php
/**
 * Created by PhpStorm.
 * User: Emanuel
 * Date: 03.10.2019
 * Time: 19:01
 */

namespace App\Service;


use App\Entity\Organisation;
use App\Entity\Stadt;
use League\Flysystem\FilesystemInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Templating\EngineInterface;
use WhiteOctober\TCPDFBundle\Controller\TCPDFController;

class PrintAGBService
{

    private $templating;

    protected $parameterBag;
    private $pdf;
    private $fileSystem;
    private $printService;
    private $tcpdf;
    public function __construct(FilesystemInterface $publicUploadsFilesystem,TCPDFController $tcpdf, EngineInterface $templating,ParameterBagInterface $parameterBag, PrintService $printService)
    {

        $this->templating = $templating;
        $this->printService = $printService;
        $this->parameterBag = $parameterBag;
        $this->pdf = $tcpdf;
        $this->fileSystem = $publicUploadsFilesystem;
        $this->tcpdf = $tcpdf;
    }

    public function printAGB($text, $type = 'D', ?Stadt $stadt=null, ?Organisation $organisation = null)
    {

        $pdf = $this->tcpdf->create();
        if($stadt){
            $pdf->setStadt($stadt);
            $fileName = 'Vertragsbedingungen '.$stadt->getSlug();


        }else{
            $fileName ='DSGVO '.$organisation->getName();
            $pdf->setOrganisation($organisation);

        }

        $pdf =$this->printService->preparePDF($pdf, $fileName, '',  $fileName, $stadt, $organisation);



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
