<?php

namespace App\Controller;

use App\Entity\Kind;
use App\Entity\Organisation;
use App\Service\CheckinSchulkindservice;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class CheckinSchulkindbetreuungController extends AbstractController
{
    /**
     * @Route("/checkin/schulkindbetreuung/{kindID}", name="checkin_schulkindbetreuung")
     */
    public function index(Request $request, TranslatorInterface $translator, $kindID, CheckinSchulkindservice $checkinSchulkindservice)
    {
        $today = (new \DateTime());
        $org = $this->getDoctrine()->getRepository(Organisation::class)->find($request->get('org_id'));
        $kind =  $this->getDoctrine()->getRepository(Kind::class)->find($kindID);

        $result = $checkinSchulkindservice->checkin($kind, $today,$org);

        return new JsonResponse($result);
    }

    /**
     * @Route("/connect/organisation/{orgID}", name="connect_Org", methods={"GET"})
     */
    public function connectOrg(Request $request, TranslatorInterface $translator, $orgID, CheckinSchulkindservice $checkinSchulkindservice)
    {
        $org = $this->getDoctrine()->getRepository(Organisation::class)->find($orgID);
        return new JsonResponse(array(
                'id' => $org->getId(),
                'name' => $org->getName(),
                'partner' => $org->getAnsprechpartner(),
                'url'=>$this->generateUrl('getOrganisationfromId',array('orgID'=>$org->getId()),UrlGeneratorInterface::ABSOLUTE_URL)
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
                'name' => $org->getName(),
                'partner' => $org->getAnsprechpartner()
            )
        );
    }
}
