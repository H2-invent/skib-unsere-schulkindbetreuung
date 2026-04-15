<?php

namespace App\Controller;

use App\Entity\Active;
use App\Entity\Schule;
use App\Service\PrintService;
use Qipsius\TCPDFBundle\Controller\TCPDFController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class DownloadFormularController extends AbstractController
{
    public function __construct(
        private LoerrachWorkflowController $loerrachWorkflowController,
    ) {
    }

    #[Route(path: '/download/anmeldung/{schule}/{cat}/{schuljahr}', name: 'download_formular_schule')]
    #[ParamConverter('schule', class: Schule::class, options: ['mapping' => ['schule' => 'id']])]
    #[ParamConverter('schuljahr', class: Active::class, options: ['mapping' => ['schuljahr' => 'id']])]
    public function index(Schule $schule, PrintService $printService, TCPDFController $TCPDFController, TranslatorInterface $translator, $cat, Active $schuljahr)
    {
        return $printService->printAnmeldeformular($schule, $TCPDFController, $translator->trans('Aenderungsformular_%n%', ['%n%' => $schule->getName()]), $this->loerrachWorkflowController->beruflicheSituation, $schule->getStadt()->getGehaltsklassen(), $cat, $schuljahr, 'D');
    }
}
