<?php

namespace App\Controller;

use App\Entity\KindFerienblock;
use App\Service\CheckinFerienService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class FerienCheckinController extends AbstractController
{
    /**
     * @Route("/org_ferien/checkin/id/{checkinID}", name="ferien_checkin",methods={"GET","POST"})
     */
    public function index(Request $request, TranslatorInterface $translator, $checkinID, CheckinFerienService $checkinFerienService)
    {
        $today = new \DateTime('today');
        $result = $checkinFerienService->checkin($checkinID, $today);
        if($request->isMethod('GET')){
             return $this->render('ferien_checkin/index.html.twig', [
                 'result'=>$result,
             ]);
        }elseif ($request->isMethod('POST')){
            return new JsonResponse($result);
        }


    }
}
