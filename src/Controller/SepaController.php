<?php

namespace App\Controller;

use App\Entity\Active;
use App\Entity\Organisation;
use App\Entity\Rechnung;
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
    public function index( Request $request, SEPASimpleService $SEPASimpleService,ValidatorInterface $validator,TranslatorInterface $translator)
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
                $result = $this->calcSepa($sepa,$translator,$SEPASimpleService);
                return $this->redirectToRoute('accounting_overview',array('id'=>$organisation->getId(),'snack'=>$result));
            }

        }

        $sepaData = $this->getDoctrine()->getRepository(Sepa::class)->findBy(array('organisation'=>$organisation));

        return $this->render('sepa/show.html.twig',array('form'=>$form->createView(),'sepa'=>$sepaData));
    }

    private function calcSepa(Sepa $sepa,TranslatorInterface $translator,SEPASimpleService $SEPASimpleService){
        $active = $this->getDoctrine()->getRepository(Active::class)->findSchuleBetweentwoDates($sepa->getVon(),$sepa->getBis(),$sepa->getOrganisation()->getStadt());
        if($sepa->getBis()<$sepa->getVon()){
            return $translator->trans('Fehler: Bis-Datum liegt vor dem Von-Datum');
        }
        if(!$active){
            return $translator->trans('Fehler: Kein Schuljahr in diesem Zeitraum gefunden');
        }
        $sepaFind = $this->getDoctrine()->getRepository(Sepa::class)->findSepaBetweenTwoDates($sepa->getVon(), $sepa->getBis(),$sepa->getOrganisation());
       if(sizeof($sepaFind)>0){
           return $translator->trans('Fehler: Es ist bereits ein SEPA-Lastschrift in diesem Zeitraum vorhanden');
       }

       $qb = $this->getDoctrine()->getRepository(Stammdaten::class)->createQueryBuilder('s');

       $qb->innerJoin('s.kinds','k')
           ->innerJoin('k.zeitblocks','zeitblocks')
           ->innerJoin('zeitblocks.schule', 'schule' )
           ->andWhere('schule.organisation = :organisation')
           ->andWhere('zeitblocks.active = :active')
           ->setParameter('active', $active)
           ->setParameter('organisation', $sepa->getOrganisation());
       $eltern = $qb->getQuery()->getResult();

        $rechnungen = array();
        $sepaSumme= 0.0;
        $organisation = $sepa->getOrganisation();
       foreach ($eltern as $data){
           $type = 'FRST';
           $ElternRechnungen = $data->getRechnungs();

           foreach ($ElternRechnungen as $data3){
               if($data3->getSepa()){
                   $type = 'RCUR';
                   break;
               }
           }

           $summe = 0.0;
           foreach ($data->getKinds() as $data2){
              if($data2->getFin()){
                $summe += $data2->getPreisforBetreuung();
              }
           }
           $rechnung = new Rechnung();
           $rechnung->setSumme($summe);
           $rechnung->setPdf('');
           $rechnung->setCreatedAt(new \DateTime());
           $rechnung->setStammdaten($data);
            $em = $this->getDoctrine()->getManager();
            $em->persist($rechnung);
            $em->flush();
           $rechnung->setRechnungsnummer('RE'.(new \DateTime())->format('Ymd').$rechnung->getId());

            $em->persist($rechnung);
            $em->flush();

           if($summe != 0){
               $rechnungen[] = $rechnung;
               $sepaSumme +=$summe;
               $sepa->addRechnungen($rechnung);
//todo check ob alle angaben richtig sind
               $SEPASimpleService->Add($sepa->getEinzugsDatum()->format('Y-m-d'), $rechnung->getSumme(), $rechnung->getStammdaten()->getKontoinhaber(), $rechnung->getStammdaten()->getIban(), $rechnung->getStammdaten()->getBic(),
                   NULL, NULL, $rechnung->getRechnungsnummer(), $rechnung->getRechnungsnummer(), $type, 'skb-'.$rechnung->getStammdaten()->getConfirmationCode(), $rechnung->getStammdaten()->getCreatedAt()->format('Y-m-d'));

           }
       }
       $sepa->setSepaXML(
           $SEPASimpleService ->GetXML('CORE', 'Einzug.'.$sepa->getEinzugsDatum()->format('d.m.Y'), 'Best.v.'.$sepa->getEinzugsDatum()->format('d.m.Y'),
            $organisation->getName(), $organisation->getName(), $organisation->getIban(), $organisation->getBic(),
            $organisation->getGlauaubigerId())
       );

        $sepa->setAnzahl(sizeof($rechnungen));
        $sepa->setCreatedAt(new \DateTime());
        $sepa->setPdf('');
        $sepa->setSumme($sepaSumme);
        $em->persist($sepa);
        $em->flush();

        return $translator->trans('Das SEPA-Lastschrift wurde erfolgreich angelegt');


    }
}
