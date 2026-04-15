<?php

namespace App\Controller;

use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Kind;
use App\Entity\KindFerienblock;
use App\Service\FerienPrintService;
use App\Service\StamdatenFromCookie;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class FerienTicketController extends AbstractController
{
    public function __construct(private ManagerRegistry $managerRegistry)
    {
    }
    /**
     * @Route("/ferien/ticket", name="ferien_ticket")
     */
    public function printAction(TranslatorInterface $translator,  FerienPrintService $print, Request $request, StamdatenFromCookie $stamdatenFromCookie)
    {

        $kind = $this->managerRegistry->getRepository(Kind::class)->findOneBy(array('id' => $request->get('kind_id')));
        $ferienblock = $this->managerRegistry->getRepository(KindFerienblock::class)->findOneBy(array('id' => $request->get('ferien_id')));

        $fileName = 'Ticket' . '_' . $kind->getVorname() . '_' . $kind->getNachname() . '.pdf';


        return $print->printPdfTicket($fileName,$ferienblock, 'D');
    }
}
