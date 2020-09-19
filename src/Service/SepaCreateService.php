<?php

namespace App\Service;

use App\Entity\Active;
use App\Entity\Organisation;
use App\Entity\Rechnung;
use App\Entity\Sepa;
use App\Entity\Stammdaten;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;
use function Doctrine\ORM\QueryBuilder;


// <- Add this

class SepaCreateService
{


    private $em;
    private $translator;
    private $sepaSimpleService;
    private $printRechnungService;
    private $mailerService;
    private $environment;

    public function __construct(Environment $environment, TranslatorInterface $translator, EntityManagerInterface $entityManager, SEPASimpleService $sepaSimpleService, PrintRechnungService $printRechnungService, MailerService $mailerService)
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

        $controleDate = clone $today;
        $controleDate->modify('+1 month');
        $controleDate->modify('first day of this month');
        if ($sepa->getBis() > $controleDate) {
            return $this->translator->trans('Fehler: Es sind nur Abrechnungen für vergangene und diesen  Monat zulässig');
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
            $re = $this->createRechnung($data, $sepa, $organisation);
            if ($re->getSumme() > 0) {
                $sepaSumme += $re->getSumme();
                $rechnungen[] = $re;
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


    private function createRechnung(Stammdaten $data, Sepa $sepa, Organisation $organisation): Rechnung
    {
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
            if ($data3->getSchule()->getOrganisation() === $organisation) {
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

        $rechnung->setCreatedAt(new \DateTime());
        $rechnung->setStammdaten($data);
        $rechnung->setPdf('');
        $this->em->persist($rechnung);
        $this->em->flush();

        $table = $this->environment->render('rechnung/tabelle.html.twig', array('rechnung' => $rechnung, 'organisation' => $organisation));
        $rechnung->setPdf($table);

        $rechnung->setRechnungsnummer('RE' . (new \DateTime())->format('Ymd') . $rechnung->getId());
        $rechnung->setSepa($sepa);
        if ($summe > 0) {

            $this->sepaSimpleService->Add($sepa->getEinzugsDatum()->format('Y-m-d'), $rechnung->getSumme(), $rechnung->getStammdaten()->getKontoinhaber(), $rechnung->getStammdaten()->getIban(), $rechnung->getStammdaten()->getBic(),
                NULL, NULL, $rechnung->getRechnungsnummer(), $rechnung->getRechnungsnummer(), $type, 'skb-' . $rechnung->getStammdaten()->getConfirmationCode(), $rechnung->getStammdaten()->getCreatedAt()->format('Y-m-d'));
        }

        return $rechnung;
    }

    public function collectallFromSepa(Sepa $sepa)
    {
        try {
            foreach ($sepa->getRechnungen() as $data) {
                $this->sendRechnung($data);
            }
            return true;
        } catch (\Exception $e) {
            return false;
        }

    }

    public function sendRechnung(Rechnung $rechnung)
    {
        $organisation = $rechnung->getSepa()->getOrganisation();
        $filename = $this->translator->trans('Rechnung') . ' ' . $rechnung->getRechnungsnummer();

        $pdf = $this->printRechnungService->printRechnung($filename, $organisation, $rechnung, 'S');
        $attachment = array();
        $attachment[] = array('type' => 'application/pdf', 'filename' => $filename . '.pdf', 'body' => $pdf);

        $mailContent = $this->environment->render('email/rechnungEmail.html.twig', array('rechnung' => $rechnung, 'organisation' => $organisation));
        $betreff = $this->translator->trans('Rechnung') . ' ' . $rechnung->getRechnungsnummer();
        $this->mailerService->sendEmail($organisation->getName(), $organisation->getEmail(), $rechnung->getStammdaten()->getEmail(), $betreff, $mailContent, $attachment);

    }

    public function diffToThisMonth(Sepa $sepa)
    {
        $active = $this->em->getRepository(Active::class)->findSchuleBetweentwoDates($sepa->getVon(), $sepa->getBis(), $sepa->getOrganisation()->getStadt());

        $qb = $this->em->getRepository(Stammdaten::class)->createQueryBuilder('s');

        $qb->innerJoin('s.kinds', 'k') // suche alles stammdaten
        ->innerJoin('k.zeitblocks', 'zeitblocks')// welche
        ->innerJoin('zeitblocks.schule', 'schule')
            ->andWhere('schule.organisation = :organisation')// wo die schule meine organisation ist
            ->andWhere('zeitblocks.active = :active')// suche alle Blöcke, wo im aktuellen SChuljahr sind
            ->andWhere('s.fin = 1')// alle Eltern sie das flag fin haben
            ->andWhere(
                $qb->expr()->between('s.created_at', ':von', ':bis')
            )// created ist vor dem jetzigen Zeitpunkt
            ->setParameter('active', $active)
            ->setParameter('organisation', $sepa->getOrganisation())
            ->setParameter('von', $sepa->getVon())
            ->setParameter('bis', $sepa->getBis());

        $eltern = $qb->getQuery()->getResult();


        $rechnungen = array();
        foreach ($eltern as $data){
            $rechnungTmp = new Rechnung();
            $rechnungTmp->setBis($sepa->getBis());
            $rechnungTmp->setVon($sepa->getVon());
            $rechnungTmp->setCreatedAt(new \DateTime());
            $rechnungTmp->setStammdaten($data);
            $summe = 0.0;
            foreach ($data->getKinds() as $data2){
                if ($data2->getSchule()->getOrganisation() == $sepa->getOrganisation()){
                    $summe += $data2->getPreisforBetreuung();
                    $rechnungTmp->addKinder($data2);
                }
            }
            $rechnungTmp->setSumme($summe);
            $rechnungen[] = $rechnungTmp;
        }

        return $rechnungen;

    }

}
