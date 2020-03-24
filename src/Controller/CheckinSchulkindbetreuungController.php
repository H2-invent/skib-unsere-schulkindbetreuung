<?php

namespace App\Controller;

use App\Entity\Organisation;
use App\Service\CheckinSchulkindservice;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class CheckinSchulkindbetreuungController extends AbstractController
{
    /**
     * @Route("/checkin/schulkindbetreuung/{kindID}", name="checkin_schulkindbetreuung")
     */
    public function index(Request $request, TranslatorInterface $translator, $kindID, CheckinSchulkindservice $checkinSchulkindservice)
    {
        $today = (new \DateTime('today'))->format('Y-m-d');
        $result = $checkinSchulkindservice->checkin($kindID, $today);

            return new JsonResponse($result);


    }
    /**
     * @Route("/connect/organisation/{orgID}", name="connect_Org", methods={"GET"})
     */
    public function connectOrg(Request $request, TranslatorInterface $translator, $orgID, CheckinSchulkindservice $checkinSchulkindservice)
    {
        $org = $this->getDoctrine()->getRepository(Organisation::class)->find($orgID);
        return new JsonResponse(array(
            'id'=>$org->getId(),
            'name'=>$org->getName(),
            'partner'=>$org->getAnsprechpartner()
            )
        );
    }

    /**
     * @Route("/get/organisation/{orgID}", name="getOrganisationfromId", methods={"GET"})
     */
    public function getORg(Request $request, TranslatorInterface $translator, $orgID, CheckinSchulkindservice $checkinSchulkindservice)
    {
        $org = $this->getDoctrine()->getRepository(Organisation::class)->find($orgID);
        return new JsonResponse(array(
                'id'=>$org->getId(),
                'name'=>$org->getName(),
                'partner'=>$org->getAnsprechpartner()
            )
        );
    }
}
