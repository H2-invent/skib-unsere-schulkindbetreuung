<?php

namespace App\Controller;

use App\Entity\Ferienblock;
use App\Entity\KindFerienblock;
use App\Entity\Organisation;
use App\Entity\Stadt;
use App\Service\PrintAGBService;
use App\Service\PrintFerienNameTagService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class FerienBerichtController extends AbstractController
{
    /**
     * @Route("/org_ferien/report/nametag", name="ferien_report_nametag")
     */
    public function nametag(Request $request, TranslatorInterface $translator, PrintFerienNameTagService $printFerienNameTagService)
    {

        $organisation = $this->getDoctrine()->getRepository(Organisation::class)->findOneBy(array('id' => $request->get('org_id')));
        $ferien = $this->getDoctrine()->getRepository(KindFerienblock::class)->findOneBy(array('id' => $request->get('ferien_id')));
        $pdf = $printFerienNameTagService->printNameTag($ferien,$organisation);

        return $pdf;
    }


}
