<?php

namespace App\Controller;

use App\Entity\Kind;
use App\Entity\KindFerienblock;
use App\Service\FerienPrintService;
use App\Service\StamdatenFromCookie;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class FerienTicketController extends AbstractController
{
    public function __construct(
        private ManagerRegistry $managerRegistry,
    ) {
    }

    #[Route(path: '/ferien/ticket', name: 'ferien_ticket')]
    public function printAction(TranslatorInterface $translator, FerienPrintService $print, Request $request, StamdatenFromCookie $stamdatenFromCookie)
    {
        $kind = $this->managerRegistry->getRepository(Kind::class)->findOneBy(['id' => $request->get('kind_id')]);
        $ferienblock = $this->managerRegistry->getRepository(KindFerienblock::class)->findOneBy(['id' => $request->get('ferien_id')]);

        $fileName = 'Ticket_' . $kind->getVorname() . '_' . $kind->getNachname() . '.pdf';

        return $print->printPdfTicket($fileName, $ferienblock, 'D');
    }
}
