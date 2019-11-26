<?php

namespace App\Controller;

use App\Entity\Active;
use App\Entity\Organisation;
use App\Entity\Rechnung;
use App\Entity\Sepa;
use App\Entity\Stadt;
use App\Entity\Stammdaten;
use App\Form\Type\SepaType;
use App\Service\MailerService;
use App\Service\PrintRechnungService;
use App\Service\SEPASimpleService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use function Doctrine\ORM\QueryBuilder;

class SepaController extends AbstractController
{
    /**
     * @Route("/org_accounting/overview", name="accounting_overview",methods={"GET","POST"})
     */
    public function index( Request $request, SEPASimpleService $SEPASimpleService,ValidatorInterface $validator,TranslatorInterface $translator,PrintRechnungService $printRechnungService,MailerService $mailerService)
    {
        set_time_limit(600);
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

            $errors = $validator->validate($sepa);
            if(count($errors)== 0) {
                $sepa = $form->getData();
                $sepa->setBis((clone $sepa->getVon())->modify('last day of this month'));
                $result = $this->calcSepa($sepa,$translator,$SEPASimpleService,$printRechnungService,$mailerService);
                return $this->redirectToRoute('accounting_overview',array('id'=>$organisation->getId(),'snack'=>$result));
            }

        }

        $sepaData = $this->getDoctrine()->getRepository(Sepa::class)->findBy(array('organisation'=>$organisation));

        return $this->render('sepa/show.html.twig',array('form'=>$form->createView(),'sepa'=>$sepaData));
    }


    private function calcSepa(Sepa $sepa,TranslatorInterface $translator,SEPASimpleService $SEPASimpleService,PrintRechnungService $printRechnungService,MailerService $mailerService){
        $active = $this->getDoctrine()->getRepository(Active::class)->findSchuleBetweentwoDates($sepa->getVon(),$sepa->getBis(),$sepa->getOrganisation()->getStadt());
        $today = new \DateTime();
        if($sepa->getBis()<$sepa->getVon()){
            return $translator->trans('Fehler: Bis-Datum liegt vor dem Von-Datum');
        }

        if($sepa->getBis() > $today){
            return $translator->trans('Fehler: Es sind nur Abrechnungen für Vergangene Monate zulässig');
        }
        if(!$active){
            return $translator->trans('Fehler: Kein Schuljahr in diesem Zeitraum gefunden');
        }
        $sepaFind = $this->getDoctrine()->getRepository(Sepa::class)->findSepaBetweenTwoDates($sepa->getVon(), $sepa->getBis(),$sepa->getOrganisation());
       if(sizeof($sepaFind)>0){
           return $translator->trans('Fehler: Es ist bereits ein SEPA-Lastschrift in diesem Zeitraum vorhanden');
       }

       $qb = $this->getDoctrine()->getRepository(Stammdaten::class)->createQueryBuilder('s');
/*     Createdat1            Createdat2  Ended_at1             Created_at3    Ended_at2   Ended_at3=null
 * =====|=====================|===========|============|==========|`=============|=============>>>>>>>>
 *                                             Suche an welchem Created At kleiner als t0 und Ended at >t0 OR Ended_at == 0
 *                                                  Objekt mit Created_at2 wird ausgewählt
 */
       $qb->innerJoin('s.kinds','k') // suche alles stammdaten
           ->innerJoin('k.zeitblocks','zeitblocks')// welche
           ->innerJoin('zeitblocks.schule', 'schule' )
           ->andWhere('schule.organisation = :organisation')// wo die schule meine organisation ist
           ->andWhere('zeitblocks.active = :active')// suche alle Blöcke, wo im aktuellen SChuljahr sind
           ->andWhere('s.saved = 1')// alle Eltern sie das flag gesaved haben
           ->andWhere('s.created_at <= :von')// created ist vor dem jetzigen Zeitpunkt
           ->andWhere(
               $qb->expr()->orX(
                   $qb->expr()->isNull('s.endedAt'),// ended ist noch offen
                   $qb->expr()->gte('s.endedAt',':von')// ended ist ende diesen monats
               )
           )
           ->setParameter('active', $active)
           ->setParameter('organisation', $sepa->getOrganisation())
            ->setParameter('von', $sepa->getVon());
       $eltern = $qb->getQuery()->getResult();

        $rechnungen = array();
        $sepaSumme= 0.0;
        $organisation = $sepa->getOrganisation();
        $em = $this->getDoctrine()->getManager();
        $sepa->setCreatedAt(new \DateTime());
        $sepa->setSumme(0);
        $sepa->setAnzahl(0);
        $sepa->setSepaXML('');
        $sepa->setPdf('');
        $em->persist($sepa);
        $em->flush();
       foreach ($eltern as $data){// für alle gefunden eltern in diesem Monat
           $type = 'FRST'; // setzte SEPA auf First Sepa

           foreach ($data->getRechnungs() as $data3){//Wenn es eine Rechnung gibt, ewlche an einem SEPA hängt,
               if($data3->getSepa()){
                   $type = 'RCUR';// dann setzte SEPA Typ auf folgenden LAstschrift SEPA
                   break;
               }
           }

           $summe = 0.0;
           $kinderDerEltern = array();
           foreach ($data->getKinds() as $data3){// nur kinder aus dieser ORganisation werden berechnet
               if($data3->getSchule()->getOrganisation()== $organisation){
                   $kinderDerEltern[] = $data3;
               }
           }
           $rechnung = new Rechnung();

           foreach ($kinderDerEltern as $data2){// berechne die summe aller kinder

                $summe += $data2->getPreisforBetreuung();


              foreach ($data2->getZeitblocks() as $zb){// füge alle ZEitblöcke an die rechnung an
                  $rechnung->addZeitblock($zb);
              }
              $rechnung->addKinder($data2);
           }

           $rechnung->setVon($sepa->getVon());
           $rechnung->setBis($sepa->getBis());
           $rechnung->setSumme($summe);

           $rechnung->setPdf('');
           $rechnung->setCreatedAt(new \DateTime());
           $rechnung->setStammdaten($data);
            $em->persist($rechnung);
            $em->flush();
            $rechnung->setRechnungsnummer('RE'.(new \DateTime())->format('Ymd').$rechnung->getId());
           $rechnung->setSepa($sepa);
           if($summe > 0){
               $rechnungen[] = $rechnung;
               $sepaSumme +=$summe;
            //todo check ob alle angaben richtig sind
               $SEPASimpleService->Add($sepa->getEinzugsDatum()->format('Y-m-d'), $rechnung->getSumme(), $rechnung->getStammdaten()->getKontoinhaber(), $rechnung->getStammdaten()->getIban(), $rechnung->getStammdaten()->getBic(),
                   NULL, NULL, $rechnung->getRechnungsnummer(), $rechnung->getRechnungsnummer(), $type, 'skb-'.$rechnung->getStammdaten()->getConfirmationCode(), $rechnung->getStammdaten()->getCreatedAt()->format('Y-m-d'));
           }
            $filename = $translator->trans('Rechnung').' '.$rechnung->getRechnungsnummer();
           $pdf = $printRechnungService->printRechnung($filename,$organisation,$rechnung,'S');
           $attachment = array();
           $attachment[] = array('type'=>'application/pdf','filename'=>$filename . '.pdf','body'=>$pdf);

           $mailContent = $this->renderView('email/rechnungEmail.html.twig',array('rechnung'=>$rechnung,'organisation'=>$organisation));
           $betreff = $translator->trans('Rechnung ').' ' .$rechnung->getRechnungsnummer();
            $mailerService->sendEmail($organisation->getName(),$organisation->getEmail(),$data->getEmail(),$betreff,$mailContent,$attachment);

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
