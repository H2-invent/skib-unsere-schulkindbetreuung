<?php

namespace App\Controller;

use App\Entity\Kind;
use App\Entity\KindFerienblock;
use App\Entity\Organisation;
use App\Service\FerienPrintService;
use App\Service\PrintService;
use App\Service\StamdatenFromCookie;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use WhiteOctober\TCPDFBundle\Controller\TCPDFController;

class FerienTicketController extends AbstractController
{
    /**
     * @Route("/ferien/ticket", name="ferien_ticket")
     */
    public function printAction(TranslatorInterface $translator, TCPDFController $tcpdf, FerienPrintService $print, Request $request, StamdatenFromCookie $stamdatenFromCookie)
    {

        $kind = $this->getDoctrine()->getRepository(Kind::class)->findOneBy(array('id' => $request->get('kind_id')));
        $ferienblock = $this->getDoctrine()->getRepository(KindFerienblock::class)->findOneBy(array('id' => $request->get('ferien_id')));
        $organisation = $this->getDoctrine()->getRepository(Organisation::class)->findOneBy(array('id' => $request->get('org_id')));

        $fileName = 'Ticket' . '_' . $kind->getVorname() . '_' . $kind->getNachname() . '.pdf';


        return $print->printPdfTicket($kind, $tcpdf, $fileName, $organisation,$ferienblock, 'D');
    }
}
