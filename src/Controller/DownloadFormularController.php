<?php

namespace App\Controller;

use App\Entity\Schule;
use App\Service\PrintService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use WhiteOctober\TCPDFBundle\Controller\TCPDFController;

class DownloadFormularController extends AbstractController
{


    /**
     * @Route("/download/anmeldung/{schule}/{cat}", name="download_formular_schule")
     * @ParamConverter("schule",class="App\Entity\Schule", options={"mapping"={"schule"="id"}})
     */
    public function index(Schule $schule, PrintService $printService, TCPDFController $TCPDFController, TranslatorInterface $translator, $cat)
    {

        return $printService->printAnmeldeformular($schule,$TCPDFController,'Änderung',(new LoerrachWorkflowController($translator))->beruflicheSituation, $schule->getStadt()->getGehaltsklassen(),$cat,'D');

    }
}
