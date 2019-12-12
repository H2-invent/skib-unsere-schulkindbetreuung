<?php

namespace App\Service;

use App\Entity\Active;
use App\Entity\Ferienblock;
use App\Entity\Kind;
use App\Entity\KindFerienblock;
use App\Entity\Organisation;
use App\Entity\Rechnung;
use App\Entity\Sepa;
use App\Entity\Stadt;

use App\Entity\Stammdaten;

use App\Entity\Zeitblock;
use App\Form\Type\ConfirmType;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use Symfony\Component\Form\Extension\Core\Type\TextType;


use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Security;

use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;


// <- Add this

class SepaCreateService
{


    private $em;
    private $translator;
    private $sepaSimpleService;
    private $printRechnungService;
    private $mailerService;
    private $environment;
    public function __construct(Environment $environment,TranslatorInterface $translator, EntityManagerInterface $entityManager,SEPASimpleService $sepaSimpleService,PrintRechnungService $printRechnungService,MailerService $mailerService)
    {
        $this->em = $entityManager;
        $this->translator = $translator;
        $this->sepaSimpleService = $sepaSimpleService;
        $this->printRechnungService = $printRechnungService;
        $this->mailerService = $mailerService;
        $this->environment = $environment;
    }

    public function calcSepa(Sepa $sepa)
    {
        $active = $this->em->getRepository(Active::class)->findSchuleBetweentwoDates($sepa->getVon(), $sepa->getBis(), $sepa->getOrganisation()->getStadt());
        $today = new \DateTime();
        if ($sepa->getBis() < $sepa->getVon()) {
            return $this->translator->trans('Fehler: Bis-Datum liegt vor dem Von-Datum');
        }

        if ($sepa->getBis() > $today) {
            return $this->translator->trans('Fehler: Es sind nur Abrechnungen für Vergangene Monate zulässig');
        }
        if (!$active) {
            return $this->translator->trans('Fehler: Kein Schuljahr in diesem Zeitraum gefunden');
        }
        $sepaFind = $this->em->getRepository(Sepa::class)->findSepaBetweenTwoDates($sepa->getVon(), $sepa->getBis(), $sepa->getOrganisation());
        if (sizeof($sepaFind) > 0) {
            return $this->translator->trans('Fehler: Es ist bereits ein SEPA-Lastschrift in diesem Zeitraum vorhanden');
        }

        $qb = $this->em->getRepository(Stammdaten::class)->createQueryBuilder('s');
        /*     Createdat1            Createdat2  Ended_at1             Created_at3    Ended_at2   Ended_at3=null
         * =====|=====================|===========|============|==========|`=============|=============>>>>>>>>
         *                                             Suche an welchem Created At kleiner als t0 und Ended at >t0 OR Ended_at == 0
         *                                                  Objekt mit Created_at2 wird ausgewählt
         */
        $qb->innerJoin('s.kinds', 'k') // suche alles stammdaten
        ->innerJoin('k.zeitblocks', 'zeitblocks')// welche
        ->innerJoin('zeitblocks.schule', 'schule')
            ->andWhere('schule.organisation = :organisation')// wo die schule meine organisation ist
            ->andWhere('zeitblocks.active = :active')// suche alle Blöcke, wo im aktuellen SChuljahr sind
            ->andWhere('s.saved = 1')// alle Eltern sie das flag gesaved haben
            ->andWhere('s.created_at <= :von')// created ist vor dem jetzigen Zeitpunkt
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->isNull('s.endedAt'),// ended ist noch offen
                    $qb->expr()->gte('s.endedAt', ':von')// ended ist ende diesen monats
                )
            )
            ->setParameter('active', $active)
            ->setParameter('organisation', $sepa->getOrganisation())
            ->setParameter('von', $sepa->getVon());
        $eltern = $qb->getQuery()->getResult();

        $rechnungen = array();
        $sepaSumme = 0.0;
        $organisation = $sepa->getOrganisation();

        $sepa->setCreatedAt(new \DateTime());
        $sepa->setSumme(0);
        $sepa->setAnzahl(0);
        $sepa->setSepaXML('');
        $sepa->setPdf('');
        $this->em->persist($sepa);
        $this->em->flush();
        foreach ($eltern as $data) {// für alle gefunden eltern in diesem Monat
            $summe = $this->createRechnung($data,$sepa,$organisation);
            if($summe>0){
                $sepaSumme += $summe;
            }
        }

        $sepa->setSepaXML(
            $this->sepaSimpleService->GetXML('CORE', 'Einzug.' . $sepa->getEinzugsDatum()->format('d.m.Y'), 'Best.v.' . $sepa->getEinzugsDatum()->format('d.m.Y'),
                $organisation->getName(), $organisation->getName(), $organisation->getIban(), $organisation->getBic(),
                $organisation->getGlauaubigerId())
        );

        $sepa->setAnzahl(sizeof($rechnungen));
        $sepa->setCreatedAt(new \DateTime());
        $sepa->setPdf('');
        $sepa->setSumme($sepaSumme);
        $this->em->persist($sepa);
        $this->em->flush();
        return $this->translator->trans('Das SEPA-Lastschrift wurde erfolgreich angelegt');
    }

    
        private function createRechnung (Stammdaten $data,Sepa $sepa, Organisation $organisation){
            $type = 'FRST'; // setzte SEPA auf First Sepa

            foreach ($data->getRechnungs() as $data3) {//Wenn es eine Rechnung gibt, ewlche an einem SEPA hängt,
                if ($data3->getSepa()) {
                    $type = 'RCUR';// dann setzte SEPA Typ auf folgenden LAstschrift SEPA
                    break;
                }
            }

            $summe = 0.0;

            $kinderDerEltern = array();
            foreach ($data->getKinds() as $data3) {// nur kinder aus dieser ORganisation werden berechnet
                if ($data3->getSchule()->getOrganisation()=== $organisation) {
                    $kinderDerEltern[] = $data3;
                }
            }
            $rechnung = new Rechnung();

            foreach ($kinderDerEltern as $data2) {// berechne die summe aller kinder

                $summe += $data2->getPreisforBetreuung();


                foreach ($data2->getZeitblocks() as $zb) {// füge alle ZEitblöcke an die rechnung an
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
            $this->em->persist($rechnung);
            $this->em->flush();
            $rechnung->setRechnungsnummer('RE' . (new \DateTime())->format('Ymd') . $rechnung->getId());
            $rechnung->setSepa($sepa);
            if ($summe > 0) {
                $rechnungen[] = $rechnung;
                //todo check ob alle angaben richtig sind
                $this->sepaSimpleService->Add($sepa->getEinzugsDatum()->format('Y-m-d'), $rechnung->getSumme(), $rechnung->getStammdaten()->getKontoinhaber(), $rechnung->getStammdaten()->getIban(), $rechnung->getStammdaten()->getBic(),
                    NULL, NULL, $rechnung->getRechnungsnummer(), $rechnung->getRechnungsnummer(), $type, 'skb-' . $rechnung->getStammdaten()->getConfirmationCode(), $rechnung->getStammdaten()->getCreatedAt()->format('Y-m-d'));
            }
            $filename = $this->translator->trans('Rechnung') . ' ' . $rechnung->getRechnungsnummer();
            $pdf = $this->printRechnungService->printRechnung($filename, $organisation, $rechnung, 'S');
            $attachment = array();
            $attachment[] = array('type' => 'application/pdf', 'filename' => $filename . '.pdf', 'body' => $pdf);

            $mailContent =   $this->environment->render('email/rechnungEmail.html.twig', array('rechnung' => $rechnung, 'organisation' => $organisation));
            $betreff = $this->translator->trans('Rechnung ') . ' ' . $rechnung->getRechnungsnummer();
           $this->mailerService->sendEmail($organisation->getName(), $organisation->getEmail(), $data->getEmail(), $betreff, $mailContent, $attachment);

            return $summe;
        }


}
