<?php

namespace App\Controller;

use App\Entity\Active;
use App\Entity\Schule;
use App\Service\PrintService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

use Qipsius\TCPDFBundle\Controller\TCPDFController;
class DownloadFormularController extends AbstractController
{
    public function __construct(
        private LoerrachWorkflowController $loerrachWorkflowController
    )
    {
    }

    /**
     * @Route("/download/anmeldung/{schule}/{cat}/{schuljahr}", name="download_formular_schule")
     * @ParamConverter("schule",class="App\Entity\Schule", options={"mapping"={"schule"="id"}})
     * @ParamConverter("schuljahr",class="App\Entity\Active", options={"mapping"={"schuljahr"="id"}})
     */
    public function index(Schule $schule, PrintService $printService, TCPDFController $TCPDFController, TranslatorInterface $translator, $cat, Active  $schuljahr)
    {

        return $printService->printAnmeldeformular($schule,$TCPDFController,$translator->trans('Aenderungsformular_%n%',array('%n%'=>$schule->getName())),$this->loerrachWorkflowController->beruflicheSituation, $schule->getStadt()->getGehaltsklassen(),$cat,$schuljahr,'D');

    }
}
