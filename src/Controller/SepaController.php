<?php

namespace App\Controller;

use App\Entity\Active;
use App\Entity\Organisation;
use App\Entity\Sepa;
use App\Entity\Stadt;
use App\Entity\Stammdaten;
use App\Form\Type\SepaType;
use App\Service\SEPASimpleService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class SepaController extends AbstractController
{
    /**
     * @Route("/org_accounting/overview", name="accounting_overview",methods={"GET","POST"})
     */
    public function index( Request $request, SEPASimpleService $sepa,ValidatorInterface $validator,TranslatorInterface $translator)
    {
        $organisation = $this->getDoctrine()->getRepository(Organisation::class)->find($request->get('id'));
        if ($organisation != $this->getUser()->getOrganisation()) {
            throw new \Exception('Wrong Organisation');
        }
        $sepa = new Sepa();
        $sepa->setOrganisation($organisation);
        $form = $this->createForm(SepaType::class, $sepa);
        $form->handleRequest($request);
        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $sepa = $form->getData();
            $errors = $validator->validate($sepa);
            if(count($errors)== 0) {
                $sepa = $this->calcSepa($sepa,$translator);
                dump($sepa);
               // $em = $this->getDoctrine()->getManager();
             //   $em->persist($sepa);
              //  $em->flush();
               // return $this->redirectToRoute('admin_stadt_edit',array('id'=>$city->getId(),'snack'=>'Erfolgreich gespeichert'));
            }

        }
        $sepaData = $this->getDoctrine()->getRepository(Sepa::class)->findBy(array('organisation'=>$organisation));


        return $this->render('sepa/show.html.twig',array('form'=>$form->createView()));
    }
    /**
     * @Route("/org_accounting/overview/sepa/new", name="sepa_overview")
     */
    public function newSepa( Request $request, SEPASimpleService $sepa)
    {
        $elter = $this->getDoctrine()->getRepository(Stammdaten::class)->findAll();
        foreach ($elter as $data) {
            $sepa->Add();
        }
        $sepa->Add('2013-09-30', 119.00, 'Kunde,Konrad', 'AT482015210000063789', 'BANKATWW123',
            NULL, NULL, '12345678', 'Rechnung 12345678', 'OOFF', 'KUN123', '2013-09-13');
        $organisation = $this->getDoctrine()->getRepository(Organisation::class)->find($request->get('organisation'));

        $xml = $sepa->GetXML('CORE', 'Einzug.2013-09', 'Best.v.13.09.2013',
            $organisation->getName(), $organisation->getName(), $organisation->getIban(), $organisation->getBic(),
            $organisation->getGlauaubigerId());
        dump($xml);
    }
    private function calcSepa(Sepa $sepa,TranslatorInterface $translator){
        $active = $this->getDoctrine()->getRepository(Active::class)->findSchuleBetweentwoDates($sepa->getVon(),$sepa->getBis(),$sepa->getOrganisation()->getStadt());
        dump($active);
        if(!$active){
            return $translator->trans('Fehler: Kein Schuljahr in diesem Zeitraum gefunden');
        }
        $sepaFind = $this->getDoctrine()->getRepository(Sepa::class)->findSepaBetweenTwoDates($sepa->getVon(), $sepa->getBis(),$sepa->getOrganisation());
        dump($sepaFind);
        $sepa->setAnzahl(5);
        $sepa->setCreatedAt(new \DateTime());
        $sepa->setPdf('');
        $sepa->setSepaXML('');
        $sepa->setSumme(12.9);
        return $sepa;
        return 0;
    }
}
