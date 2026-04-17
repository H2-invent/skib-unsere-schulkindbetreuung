<?php

/**
 * Created by PhpStorm.
 * User: Emanuel
 * Date: 03.10.2019
 * Time: 19:01.
 */

namespace App\Service;

use App\Entity\Organisation;
use Qipsius\TCPDFBundle\Controller\TCPDFController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Twig\Environment;

class PrintFerienNameTagService
{
    protected $parameterBag;

    public function __construct(
        private TCPDFController $pdf,
        private Environment $templating,
        ParameterBagInterface $parameterBag,
    ) {
        $this->parameterBag = $parameterBag;
    }

    public function printNameTag($kinder, Organisation $organisation, $type = 'D')
    {
        $pdf = $this->pdf->create('vertical', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->setFontSubsetting(true);

        $pdf->SetFont('freeserif', '', 16);
        $fileName = '';

        $pdf->SetAuthor($fileName);
        $pdf->SetTitle($fileName);
        $pdf->SetSubject($fileName);
        $pdf->SetMargins(10, 10, 10, true);
        $pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);
        $pdf->setHeaderData('', 0, '', '', [0, 0, 0], [255, 255, 255]);
        $pdf->setFooterData(1, 1);

        $pdf->AddPage();

        $pdf->setJPEGQuality(75);
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        $fileName = 'Namenschilder ' . $organisation->getName();

        $render_kind = array_chunk($kinder, 2, true);
        $content = $this->templating->render('ferien_bericht/namenTags.html.twig', ['render_kind' => $render_kind]);
        $pdf->writeHTMLCell(0, 0, 0, 0, $content, 0, 1, 0, true, '', true
        );

        return $pdf->Output($fileName . '.pdf', $type); // This will output the PDF as a Download
    }
}
