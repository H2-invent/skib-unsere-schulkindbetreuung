<?php

/**
 * Created by PhpStorm.
 * User: Emanuel
 * Date: 03.10.2019
 * Time: 19:01.
 */

namespace App\Service;

use App\Entity\Organisation;
use App\Entity\Stadt;
use Qipsius\TCPDFBundle\Controller\TCPDFController;
use Twig\Environment;

class PrintDatenschutzService
{
    public function __construct(
        private TCPDFController $tcpdf,
        private Environment $templating,
        private PrintService $printService,
    )
    {
    }

    public function printDatenschutz($text, $type = 'D', ?Stadt $stadt = null, ?Organisation $organisation = null)
    {
        $pdf = $this->tcpdf->create();
        if ($stadt) {
            $pdf->setStadt($stadt);
            $fileName = 'Datenschutzbestimmung_ ' . $stadt->getName();
        } else {
            $fileName = 'Datenschutz ' . $organisation->getName();
            $pdf->setOrganisation($organisation);
        }

        $pdf = $this->printService->preparePDF($pdf, $fileName, '', $fileName, $stadt, $organisation);

        $table = $this->templating->render('pdf/agb.html.twig', ['text' => $text]);
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

        return $pdf->Output($fileName . '.pdf', $type); // This will output the PDF as a Download
    }
}
