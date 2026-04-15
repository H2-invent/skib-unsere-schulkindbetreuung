<?php

namespace App\Controller;

use App\Entity\Active;
use App\Entity\Schule;
use App\Service\PrintService;
use Qipsius\TCPDFBundle\Controller\TCPDFController;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class DownloadFormularController extends AbstractController
{
    public function __construct(
        private LoerrachWorkflowController $loerrachWorkflowController,
    ) {
    }

    #[Route(path: '/download/anmeldung/{schule}/{cat}/{schuljahr}', name: 'download_formular_schule')]
    public function index(#[MapEntity(class: Schule::class, mapping: ['schule' => 'id'])] Schule $schule, PrintService $printService, TCPDFController $TCPDFController, TranslatorInterface $translator, $cat, #[MapEntity(class: Active::class, mapping: ['schuljahr' => 'id'])] Active $schuljahr)
    {
        return $printService->printAnmeldeformular($schule, $TCPDFController, $translator->trans('Aenderungsformular_%n%', ['%n%' => $schule->getName()]), $this->loerrachWorkflowController->beruflicheSituation, $schule->getStadt()->getGehaltsklassen(), $cat, $schuljahr, 'D');
    }
}
