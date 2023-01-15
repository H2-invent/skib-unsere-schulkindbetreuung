<?php

namespace App\Service;

use App\Entity\Active;
use App\Entity\Kind;
use App\Entity\Organisation;
use App\Entity\Rechnung;
use App\Entity\Sepa;
use App\Entity\Stammdaten;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemOperator;
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
    private FilesystemOperator $internFileSystem;
    private BerechnungsService $berechnungsService;
    private ElternService $elternService;

    public function __construct(FilesystemOperator     $internFileSystem,
                                Environment            $environment,
                                TranslatorInterface    $translator,
                                EntityManagerInterface $entityManager,
                                SEPASimpleService      $sepaSimpleService,
                                PrintRechnungService   $printRechnungService,
                                MailerService          $mailerService,
                                BerechnungsService     $berechnungsService,
                                ElternService          $elternService)
    {
        $this->em = $entityManager;
        $this->translator = $translator;
        $this->sepaSimpleService = $sepaSimpleService;
        $this->printRechnungService = $printRechnungService;
        $this->mailerService = $mailerService;
        $this->environment = $environment;
        $this->internFileSystem = $internFileSystem;
        $this->berechnungsService = $berechnungsService;
        $this->elternService = $elternService;

    }

    /**
     * @param Sepa $sepa
     * @return Sepa|string
     */
    public function createSepa(Sepa $sepa)
    {
        $sepa = $this->calcSepa($sepa);
        if ($sepa instanceof Sepa) {
            $this->em->persist($sepa);
            $this->em->flush();
            return $this->translator->trans('Das SEPA-Lastschrift wurde erfolgreich angelegt');
        }
        return $sepa;

    }

    /**
     * @param Sepa $sepa
     * @return Sepa|string
     */
    public function calcSepa(Sepa $sepa, $demoMode = false)
    {

        $active = $this->em->getRepository(Active::class)->findSchuljahrBetweentwoDates($sepa->getVon(), $sepa->getBis(), $sepa->getOrganisation()->getStadt());
        $today = new \DateTime();
        if ($sepa->getBis() < $sepa->getVon()) {
            return $this->translator->trans('Fehler: Bis-Datum liegt vor dem Von-Datum');
        }

        $controleDate = clone $today;
        $controleDate->modify('+1 month');
        $controleDate->modify('first day of this month');
        if ($sepa->getBis() > $controleDate && $demoMode === false) {
            return $this->translator->trans('Fehler: Es sind nur Abrechnungen für vergangene und diesen  Monat zulässig');
        }

        if (!$active) {
            return $this->translator->trans('Fehler: Kein Schuljahr in diesem Zeitraum gefunden');
        }
        $sepaFind = $this->em->getRepository(Sepa::class)->findSepaBetweenTwoDates($sepa->getVon(), $sepa->getBis(), $sepa->getOrganisation());
        if (sizeof($sepaFind) > 0 && $demoMode === false) {
            return $this->translator->trans('Fehler: Es ist bereits ein SEPA-Lastschrift in diesem Zeitraum vorhanden');
        }


        $stammdatenQb = $this->em->getRepository(Stammdaten::class)->createQueryBuilder('s');
        $stammdatenQb->innerJoin('s.kinds', 'kinds')
            ->innerJoin('kinds.schule', 'schule')
            ->andWhere('schule.organisation = :organisation')->setParameter('organisation', $sepa->getOrganisation())
            ->innerJoin('kinds.zeitblocks', 'zeitblocks')
            ->andWhere('zeitblocks.active = :active')->setParameter('active', $active)// suche alle Blöcke, wo im aktuellen SChuljahr sind
            ->andWhere('zeitblocks.deleted != TRUE')
            ->andWhere('s.created_at IS NOT NULL')
            ->andWhere('s.startDate IS NOT NULL')
            ->andWhere('s.startDate <= :datetime')->setParameter('datetime', $sepa->getVon());
        $stamdaten = $stammdatenQb->getQuery()->getResult();

        $stammdatenRes = array();
        foreach ($stamdaten as $data) {
            $stammdatenTmp = isset($stammdatenRes[$data->getTracing()]) ? $stammdatenRes[$data->getTracing()] : null;
            if (!$stammdatenTmp) {
                $stammdatenRes[$data->getTracing()] = $data;
            }
        }


        $organisation = $sepa->getOrganisation();

        $sepa->setCreatedAt(new \DateTime());
        $sepa->setSumme(0);
        $sepa->setAnzahl(0);
        $sepa->setSepaXML('');
        $sepa->setPdf('');

        foreach ($stammdatenRes as $data) {
            $rechnung = $this->createRechnungFromStammdaten($data, $sepa, $sepa->getVon());
            $sepa->addRechnungen($rechnung);
        }

        $sepa = $this->fillSepa($sepa);

        $sepa->setSepaXML(
            $this->sepaSimpleService->GetXML('CORE', 'Einzug.' . $sepa->getEinzugsDatum()->format('d.m.Y'), 'Best.v.' . $sepa->getEinzugsDatum()->format('d.m.Y'),
                $organisation->getName(), $organisation->getName(), $organisation->getIban(), $organisation->getBic(),
                $organisation->getGlauaubigerId())
        );

        $sepa->setCreatedAt(new \DateTime());
        $sepa->setPdf('');

        return $sepa;
    }


    private function createRechnungFromKind(Kind $kind, Sepa $sepa, Organisation $organisation): Rechnung
    {
        $type = 'FRST'; // setzte SEPA auf First Sepa
        $eltern = $this->elternService->getLatestElternFromChild($kind);

        $otherSepa = $this->em->getRepository(Sepa::class)->findOtherSepaBySepaAndStammdaten($eltern, $sepa);

        if (sizeof($otherSepa) > 0) {
            $type = 'RCUR';// dann setzte SEPA Typ auf folgenden LAstschrift SEPA
        }


        $rechnung = null;


        foreach ($sepa->getRechnungen() as $data) {
            if ($data->getStammdaten()->getTracing() === $eltern->getTracing()) {
                $rechnung = $data;
            }
        }

        if (!$rechnung) {
            $rechnung = new Rechnung();
            $rechnung->setSumme(0.0);
            $rechnung->setVon($sepa->getVon());
            $rechnung->setBis($sepa->getBis());
            $rechnung->setCreatedAt(new \DateTime());
            $rechnung->setStammdaten($eltern);
            $rechnung->setPdf('');
            $rechnung->setSepa($sepa);
            $sepa->addRechnungen($rechnung);
            $rechnung->setRechnungsnummer('RE' . (new \DateTime())->format('Ymd') . $rechnung->getId());
            $rechnung->setSepaType($type);
        }

        foreach ($kind->getRealZeitblocks() as $data) {
            $rechnung->addZeitblock($data);
        }

        $rechnung->addKinder($kind);
        $rechnung->setSumme($rechnung->getSumme() + $this->berechnungsService->getPreisforBetreuung($kind, false));


        $table = $this->environment->render('rechnung/tabelle.html.twig', array('rechnung' => $rechnung, 'organisation' => $organisation));
        $rechnung->setPdf($table);

        return $rechnung;
    }

    private function createRechnungFromStammdaten(Stammdaten $stammdaten, Sepa $sepa, \DateTime $dateTime): Rechnung
    {
        $type = 'FRST'; // setzte SEPA auf First Sepa


        $otherSepa = $this->em->getRepository(Sepa::class)->findOtherSepaBySepaAndStammdaten($stammdaten, $sepa);

        if (sizeof($otherSepa) > 0) {
            $type = 'RCUR';// dann setzte SEPA Typ auf folgenden LAstschrift SEPA
        }

        $rechnung = new Rechnung();
        $rechnung->setSumme(0.0);
        $rechnung->setVon($sepa->getVon());
        $rechnung->setBis($sepa->getBis());
        $rechnung->setCreatedAt(new \DateTime());
        $rechnung->setStammdaten($stammdaten);
        $rechnung->setPdf('');
        $rechnung->setSepa($sepa);
        $sepa->addRechnungen($rechnung);
        $rechnung->setRechnungsnummer('RE' . (new \DateTime())->format('Ymd') . $rechnung->getId());
        $rechnung->setSepaType($type);
        $stammdaten = $this->elternService->getElternForSpecificTimeAndStammdaten($stammdaten, $dateTime);
        $kinder = $this->elternService->getKinderProStammdatenAnEinemZeitpunkt($stammdaten, $dateTime);

        foreach ($kinder as $kind) {

            foreach ($kind->getRealZeitblocks() as $data) {
                $rechnung->addZeitblock($data);
            }
            $rechnung->addKinder($kind);
            $rechnung->setSumme($rechnung->getSumme() + $this->berechnungsService->getPreisforBetreuung($kind, false, $dateTime));
        }


        $table = $this->environment->render('rechnung/tabelle.html.twig', array('rechnung' => $rechnung, 'organisation' => $sepa->getOrganisation()));
        $rechnung->setPdf($table);

        return $rechnung;
    }

    public function fillSepa(Sepa $sepa)
    {
        $summe = 0.0;
        $count = 0;
        foreach ($sepa->getRechnungen() as $rechnung) {
            if ($rechnung->getSumme() > 0) {
                $this->sepaSimpleService->Add($sepa->getEinzugsDatum()->format('Y-m-d'), $rechnung->getSumme(), $rechnung->getStammdaten()->getKontoinhaber(), $rechnung->getStammdaten()->getIban(), $rechnung->getStammdaten()->getBic(),
                    NULL, NULL, $rechnung->getRechnungsnummer(), $rechnung->getRechnungsnummer(), $rechnung->getSepaType(), 'skb-' . $rechnung->getStammdaten()->getConfirmationCode(), $rechnung->getStammdaten()->getCreatedAt()->format('Y-m-d'));
                $summe += $rechnung->getSumme();
                $count++;
            }
        }
        $sepa->setSumme($summe);
        $sepa->setAnzahl($count);
        return $sepa;
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
        foreach ($organisation->getStadt()->getEmailDokumenteRechnung() as $att) {
            $attachment[] = array(
                'body' => $this->internFileSystem->read($att->getFileName()),
                'filename' => $att->getOriginalName(),
                'type' => $att->getType()
            );
        }
        $this->mailerService->sendEmail(
            $organisation->getName(),
            $organisation->getEmail(),
            $rechnung->getStammdaten()->getEmail(),
            $betreff,
            $mailContent,
            $organisation->getEmail(),
            $attachment
        );

    }

    public function diffToThisMonth(Sepa $sepa, \DateTime $pickday)
    {

        $sepaDummy = new Sepa();
        $sepaDummy->setVon($pickday);
        $sepaDummy->setEinzugsDatum(new \DateTime());
        $sepaDummy->setOrganisation($sepa->getOrganisation());
        $sepaDummy->setBis((clone $sepaDummy->getVon())->modify('last day of this month'));
        $sepaDummy = $this->calcSepa($sepaDummy, true);
        $rechnungenOriginal = $sepa->getRechnungen();
        $rechnungenDummy = $sepaDummy->getRechnungen();

        $rechnungen = array();

        foreach ($rechnungenOriginal as $data) {
            $found = false;
            foreach ($rechnungenDummy as $data2) {
                if ($data2->getStammdaten()->getTracing() === $data->getStammdaten()->getTracing()) {
                    $diff = round($data2->getSumme() - $data->getSumme(), 2);
                    if ($diff !== 0.0) {
                        $rechnungen[] = $data2;
                    }
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $rechnungen[] = $data->setSumme(0);
            }

        }

        foreach ($rechnungenDummy as $data) {
            $found = false;
            foreach ($rechnungenOriginal as $data2) {
                if ($data2->getStammdaten()->getTracing() === $data->getStammdaten()->getTracing()) {
                    $found = true;
                    break;

                }
            }
            if (!$found) {
                $rechnungen[] = $data;
            }

        }


        return $rechnungen;

    }

}
