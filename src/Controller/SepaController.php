<?php

namespace App\Controller;

use App\Entity\Organisation;
use App\Entity\Stammdaten;
use App\Service\SEPASimpleService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SepaController extends AbstractController
{
    /**
     * @Route("/org_accounting/sepa/overview", name="sepa_overview")
     */
    public function index( Request $request, SEPASimpleService $sepa)
    {
        $elter = $this->getDoctrine()->getRepository(Stammdaten::class)->findAll();
        foreach ($elter as $data){
            $sepa->Add();
        }
        $sepa->Add('2013-09-30', 119.00, 'Kunde,Konrad', 'AT482015210000063789', 'BANKATWW123',
            NULL, NULL, '12345678', 'Rechnung 12345678', 'OOFF', 'KUN123', '2013-09-13');
        $organisation = $this->getDoctrine()->getRepository(Organisation::class)->find($request->get('organisation'));

        $xml = $sepa->GetXML('CORE', 'Einzug.2013-09', 'Best.v.13.09.2013',
            $organisation->getName(), $organisation->getName(), $organisation->getIban(), $organisation->getBic(),
            $organisation->getGlauaubigerId());
        dump($xml);

        return new Response($xml) ;

    }
}
