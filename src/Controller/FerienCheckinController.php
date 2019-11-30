<?php

namespace App\Controller;

use App\Entity\KindFerienblock;
use App\Service\CheckinFerienService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class FerienCheckinController extends AbstractController
{
    /**
     * @Route("/org_ferien/checkin/{checkinID}", name="ferien_checkin")
     */
    public function index(TranslatorInterface $translator, $checkinID, CheckinFerienService $checkinFerienService)
    {
        $result = $checkinFerienService->checkin($checkinID);

        return $this->render('ferien_checkin/index.html.twig', [
            'result'=>$result,
        ]);
    }
}
