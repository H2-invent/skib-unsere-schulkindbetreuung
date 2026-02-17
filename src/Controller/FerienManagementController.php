<?php

namespace App\Controller;

use App\Entity\Ferienblock;
use App\Entity\Kind;
use App\Entity\KindFerienblock;
use App\Entity\Organisation;
use App\Entity\Stammdaten;
use App\Form\Type\FerienBlockPreisType;
use App\Form\Type\FerienBlockType;
use App\Form\Type\FerienBlockVoucherType;
use App\Form\Type\OrganisationFerienType;
use App\Repository\FerienblockRepository;
use App\Repository\KindFerienblockRepository;
use App\Repository\KindRepository;
use App\Service\AnwesenheitslisteService;
use App\Service\CheckinFerienService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use function Doctrine\ORM\QueryBuilder;

class FerienManagementController extends AbstractController
{
    public function __construct(private \Doctrine\Persistence\ManagerRegistry $managerRegistry)
    {
    }

    /**
     * @Route("/org_ferien/edit/show", name="ferien_management_show",methods={"GET"})
     */
    public function index(Request $request)
    {

        $organisation = $this->managerRegistry->getRepository(Organisation::class)->find($request->get('org_id'));
        if ($organisation != $this->getUser()->getOrganisation()) {
            throw new \Exception('Wrong Organisation');
        }
        $blocks = $this->managerRegistry->getRepository(Ferienblock::class)->findBy(array('organisation' => $organisation), array('startDate' => 'asc'));

        return $this->render('ferien_management/index.html.twig', array('blocks' => $blocks, 'org' => $organisation));
    }


    /**
     * @Route("/org_ferien/edit/neu", name="ferien_management_neu", methods={"GET","POST"})
     */
    public function neu(Request $request, ValidatorInterface $validator, TranslatorInterface $translator)
    {
        $organisation = $this->managerRegistry->getRepository(Organisation::class)->find($request->get('org_id'));
        if ($organisation != $this->getUser()->getOrganisation()) {
            throw new \Exception('Wrong Organisation');
        }
        $ferienblock = new Ferienblock();
        $ferienblock->setOrganisation($organisation);
        $ferienblock->setStadt($organisation->getStadt());
        $form = $this->createForm(FerienBlockType::class, $ferienblock);

        $form->handleRequest($request);

        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $block = $form->getData();
            $errors = $validator->validate($block);
            if (count($errors) == 0) {
                $em = $this->managerRegistry->getManager();
                $em->persist($block);
                $em->flush();
                $text = $translator->trans('Erfolgreich angelegt');
                return $this->redirectToRoute('ferien_management_preise', array('ferien_id' => $block->getId(), 'org_id' => $organisation->getId(), 'snack' => $text));
            }

        }
        $title = $translator->trans('Ferienprogramm erstellen');
        return $this->render('administrator/neu.html.twig', array('title' => $title, 'form' => $form->createView(), 'errors' => $errors));

    }


    /**
     * @Route("/org_ferien/edit/preise", name="ferien_management_preise", methods={"GET","POST"})
     */
    public function preise(Request $request, ValidatorInterface $validator, TranslatorInterface $translator)
    {
        $organisation = $this->managerRegistry->getRepository(Organisation::class)->find($request->get('org_id'));
        if ($organisation != $this->getUser()->getOrganisation()) {
            throw new \Exception('Wrong Organisation');
        }
        $ferienblock = $this->managerRegistry->getRepository(Ferienblock::class)->findOneBy(array('id' => $request->get('ferien_id'), 'organisation' => $organisation));
        if ($ferienblock->getPreis() === null || $ferienblock->getNamePreise() === null || sizeof($ferienblock->getPreis()) != $ferienblock->getAnzahlPreise()) {
            $ferienblock->setNamePreise(array_fill(0, $ferienblock->getAnzahlPreise(), ''));
            $ferienblock->setPreis(array_fill(0, $ferienblock->getAnzahlPreise(), 0));
        }

        $form = $this->createForm(FerienBlockPreisType::class, $ferienblock);
        $form->handleRequest($request);
        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $block = $form->getData();
            $errors = $validator->validate($block);
            if (count($errors) == 0) {
                $em = $this->managerRegistry->getManager();
                $em->persist($block);
                $em->flush();
                $text = $translator->trans('Erfolgreich geändert');
                return $this->redirectToRoute('ferien_management_show', array('org_id' => $organisation->getId(), 'snack' => $text));
            }

        }
        $title = $translator->trans('Preise bearbeiten');
        return $this->render('administrator/neu.html.twig', array('title' => $title, 'form' => $form->createView(), 'errors' => $errors));
    }


    /**
     * @Route("/org_ferien/edit/voucher", name="ferien_management_voucher", methods={"GET","POST"})
     */
    public function ferienblockVoucher(Request $request, ValidatorInterface $validator, TranslatorInterface $translator)
    {
        $organisation = $this->managerRegistry->getRepository(Organisation::class)->find($request->get('org_id'));
        if ($organisation != $this->getUser()->getOrganisation()) {
            throw new \Exception('Wrong Organisation');
        }
        $ferienblock = $this->managerRegistry->getRepository(Ferienblock::class)->findOneBy(array('id' => $request->get('ferien_id'), 'organisation' => $organisation));

        if ($ferienblock->getVoucher() === null || $ferienblock->getVoucherPrice() === null) {
            $ferienblock->setVoucher(array_fill(0, $ferienblock->getAmountVoucher(), ''));
            $ferienblock->setVoucherPrice(array_fill(0, $ferienblock->getAmountVoucher(), '99'));
        }
        $size = sizeof($ferienblock->getVoucher());
        if ($size != $ferienblock->getAmountVoucher()) {
            array_push($ferienblock->getVoucher(), '');
            $ferienblock->setVoucherPrice(array_fill($size + 1, $ferienblock->getAmountVoucher() - $size, '99'));
        }

        $form = $this->createForm(FerienBlockVoucherType::class, $ferienblock);
        $form->handleRequest($request);
        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $block = $form->getData();
            $errors = $validator->validate($block);
            if (count($errors) == 0) {
                $em = $this->managerRegistry->getManager();
                $em->persist($block);
                $em->flush();
                $text = $translator->trans('Erfolgreich geändert');
                return $this->redirectToRoute('ferien_management_show', array('org_id' => $organisation->getId(), 'snack' => $text));
            }

        }
        $title = $translator->trans('Gutscheine bearbeiten');
        return $this->render('administrator/neu.html.twig', array('title' => $title, 'form' => $form->createView(), 'errors' => $errors));
    }


    /**
     * @Route("/org_ferien/edit/edit", name="ferien_management_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, ValidatorInterface $validator, TranslatorInterface $translator)
    {
        $organisation = $this->managerRegistry->getRepository(Organisation::class)->find($request->get('org_id'));
        if ($organisation != $this->getUser()->getOrganisation()) {
            throw new \Exception('Wrong Organisation');
        }

        $ferienblock = $this->managerRegistry->getRepository(Ferienblock::class)->findOneBy(array('id' => $request->get('ferien_id'), 'organisation' => $organisation));

        $form = $this->createForm(FerienBlockType::class, $ferienblock);

        $form->handleRequest($request);

        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $block = $form->getData();

            $errors = $validator->validate($block);
            if (count($errors) == 0) {
                $em = $this->managerRegistry->getManager();
                $em->persist($block);
                $em->flush();
                $text = $translator->trans('Erfolgreich geändert');
                return $this->redirectToRoute('ferien_management_show', array('org_id' => $organisation->getId(), 'snack' => $text));
            }

        }
        $title = $translator->trans('Ferienprogramm bearbeiten');
        return $this->render('administrator/neu.html.twig', array('title' => $title, 'form' => $form->createView(), 'errors' => $errors));

    }


    /**
     * @Route("/org_ferien/edit/delete", name="ferien_management_delete", methods={"DELETE"})
     */
    public function delte(Request $request, ValidatorInterface $validator, TranslatorInterface $translator)
    {
        $organisation = $this->managerRegistry->getRepository(Organisation::class)->find($request->get('org_id'));
        if ($organisation != $this->getUser()->getOrganisation()) {
            throw new \Exception('Wrong Organisation');
        }

        $ferienblock = $this->managerRegistry->getRepository(Ferienblock::class)->findOneBy(array('id' => $request->get('ferien_id'), 'organisation' => $organisation));
        $em = $this->managerRegistry->getManager();
        $em->remove($ferienblock);
        $em->flush();
        $text = $translator->trans('Erfolgreich gelöscht');
        return new JsonResponse(array('redirect' => $this->generateUrl('ferien_management_show', array('org_id' => $organisation->getId(), 'snack' => $text))));
    }


    /**
     * @Route("/org_ferien/duplicate", name="ferien_management_duplicate", methods={"POST"})
     */
    public function duplicate(Request $request, ValidatorInterface $validator, TranslatorInterface $translator)
    {
        $organisation = $this->managerRegistry->getRepository(Organisation::class)->find($request->get('org_id'));
        if ($organisation != $this->getUser()->getOrganisation()) {
            throw new \Exception('Wrong Organisation');
        }

        $ferienblock = $this->managerRegistry->getRepository(Ferienblock::class)->findOneBy(array('id' => $request->get('ferien_id'), 'organisation' => $organisation));
        $ferienblockNew = clone $ferienblock;
        $translations = $ferienblock->getTranslations();
        foreach ($translations as $locale => $fields) {
            $clone = clone $fields;
            $clone->setTitel('[copy]' . $clone->getTitel());
            $ferienblockNew->addTranslation($clone);

        }
        foreach ($ferienblock->getKategorie() as $data) {
            $ferienblockNew->addKategorie($data);
        }
        $em = $this->managerRegistry->getManager();
        $em->persist($ferienblockNew);
        $em->flush();
        $text = $translator->trans('Erfolgreich kopiert');
        return new JsonResponse(array('redirect' => $this->generateUrl('ferien_management_edit', array('org_id' => $organisation->getId(), 'ferien_id' => $ferienblockNew->getId(), 'snack' => $text))));

    }


    /**
     * @Route("/org_ferien/checkin/list", name="ferien_management_report_checkinlist", methods={"GET"})
     */
    public function checkinListFerien(Request $request, TranslatorInterface $translator)
    {
        $organisation = $this->managerRegistry->getRepository(Organisation::class)->find($request->get('org_id'));
        $block = $this->managerRegistry->getRepository(Ferienblock::class)->find($request->get('ferien_id'));

        if ($organisation != $block->getOrganisation()) {
            throw new \Exception('Organisation not responsible for block');
        }

        if ($organisation != $this->getUser()->getOrganisation()) {
            throw new \Exception('Wrong Organisation');
        }

        $today = new \DateTime('today');
        $checkinDate = $today->format('Y-m-d');
        $kinder = $this->managerRegistry->getRepository(KindFerienblock::class)->findOrdersByBlock($block);
        $titel = $translator->trans('Anwesenheitsliste für Ferienblock');
        $mode = 'order';

        return $this->render('ferien_management/checkinList.html.twig', [
            'org' => $organisation,
            'list' => $kinder,
            'day' => $checkinDate,
            'titel' => $titel,
            'mode' => $mode,
        ]);
    }


    /**
     * @Route("/org_ferien/orders", name="ferien_management_orders", methods={"GET"})
     */
    public function ordersOverview(Request $request, TranslatorInterface $translator)
    {
        $organisation = $this->managerRegistry->getRepository(Organisation::class)->find($request->get('org_id'));

        if ($organisation != $this->getUser()->getOrganisation()) {
            throw new \Exception('Wrong Organisation');
        }

        $qb = $this->managerRegistry->getRepository(Stammdaten::class)->createQueryBuilder('stammdaten');
        $qb->innerJoin('stammdaten.kinds', 'kinds')
            ->innerJoin('kinds.kindFerienblocks', 'kind_ferienblocks')
            ->innerJoin('kind_ferienblocks.ferienblock', 'ferienblock')
            ->andWhere('ferienblock.organisation = :org')
            ->andWhere('stammdaten.fin = true')
            ->setParameter('org', $organisation);
        $query = $qb->getQuery();
        $stammdaten = $query->getResult();
        $titel = $translator->trans('Alle Anmeldungen');

        return $this->render('ferien_management/orderList.html.twig', [
            'org' => $organisation,
            'stammdaten' => $stammdaten,
            'titel' => $titel,
        ]);
    }

    /**
     * @Route("/org_ferien/orders/storno", name="ferien_management_orders_storno", methods={"GET"})
     */
    public function storno(Request $request, TranslatorInterface $translator)
    {
        $organisation = $this->managerRegistry->getRepository(Organisation::class)->find($request->get('org_id'));
        $stammdaten = $this->managerRegistry->getRepository(Stammdaten::class)->findOneBy(array('uid' => $request->get('parent_id')));

        if ($organisation != $this->getUser()->getOrganisation()) {
            throw new \Exception('Wrong Organisation');
        }

        return $this->redirectToRoute('ferien_storno', array('slug' => $organisation->getStadt()->getSlug(), 'parent_id' => $stammdaten->getUid()));
    }


    /**
     * @Route("/org_ferien/checkin/list/tag", name="ferien_management_report_checkinlist_tag", methods={"GET"})
     */
    public function checkinListTagyFerien(Request $request, TranslatorInterface $translator, AnwesenheitslisteService $anwesenheitslisteService)
    {
        $organisation = $this->managerRegistry->getRepository(Organisation::class)->find($request->get('org_id'));
        if ($organisation != $this->getUser()->getOrganisation()) {
            throw new \Exception('Wrong Organisation');
        }
        $tag = $request->get('tag');
        $selectDate = null;
        if ($tag === null) {
            $today = new \DateTime('today');
            $selectDate = $today->setTime(0, 0);
        } else {
            $selectDate = new \DateTime($tag);
            $selectDate->setTime(0, 0);
        }
        $kind = $anwesenheitslisteService->anwesenheitsListe($selectDate, $organisation);

        $titel = $translator->trans('Anwesenheitsliste');
        $mode = 'day';
        return $this->render('ferien_management/checkinList.html.twig', [
            'org' => $organisation,
            'list' => $kind,
            'day' => $selectDate,
            'titel' => $titel,
            'mode' => $mode,
        ]);
    }


    /**
     * @Route("/org_ferien/checkin/toggle/{checkinID}", name="ferien_management_report_checkin_toggle", methods={"PATCH"})
     */
    public function checkinBlockAction(Request $request, TranslatorInterface $translator, $checkinID, CheckinFerienService $checkinFerienService)
    {
        $result = $checkinFerienService->checkin($checkinID, $request->get('tag'));

        return new JsonResponse($result);
    }


    /**
     * @Route("/org_ferien/orders/detail", name="ferien_management_order_detail", methods={"GET"})
     */
    public function orderDetails(Request $request, TranslatorInterface $translator, AnwesenheitslisteService $anwesenheitslisteService)
    {
        $organisation = $this->managerRegistry->getRepository(Organisation::class)->find($request->get('org_id'));
        $stammdaten = $this->managerRegistry->getRepository(Stammdaten::class)->find($request->get('id'));
        if ($organisation != $this->getUser()->getOrganisation()) {
            throw new \Exception('Wrong Organisation');
        }
        $qb = $this->managerRegistry->getRepository(Kind::class)->createQueryBuilder('kind')
            ->innerJoin('kind.kindFerienblocks', 'kind_ferienblocks')
            ->innerJoin('kind_ferienblocks.ferienblock', 'ferienblock')
            ->andWhere('ferienblock.organisation = :org')
            ->andWhere('kind.eltern = :stammdaten')
            ->setParameter('org', $organisation)
            ->setParameter('stammdaten', $stammdaten);
        $query = $qb->getQuery();
        $kinds = $query->getResult();
        $titel = $translator->trans('Details');

        return $this->render('ferien_management/details.html.twig', [
            'org' => $organisation,
            'stammdaten' => $stammdaten,
            'titel' => $titel,
            'kinds' => $kinds,
        ]);
    }

    /**
     * @Route("/org_ferien/orders/{kurs_id}/sepa-export.csv", name="admin_sepa_export_kurs_csv", methods={"GET"})
     */
    public function exportKursSepaCsv(
        int                       $kurs_id,
        FerienblockRepository     $kursRepo,
        KindFerienblockRepository $bookingRepo
    )
    {
        // optional: Rechteprüfung
        // $this->denyAccessUnlessGranted('ROLE_ORG_FERIEN_SEPA_EXPORT');

        $org = $this->getUser()->getOrganisation();


        $kurs = $kursRepo->find($kurs_id);
        if (!$kurs) {
            throw $this->createNotFoundException('Kurs nicht gefunden');
        }
        $buchungen = $kurs->getKindFerienblocksGebucht();
        $buchungen_real = [];
        $eltern = [];
        foreach ($buchungen as $b) {
            $parent = $b->getKind()?->getEltern();
            if (!$parent) {
                continue;
            }

            $pid = $parent->getId();

            if (!isset($eltern[$pid])) {
                $eltern[$pid] = [
                    'eltern' => $parent,
                    'buchungen' => [],
                ];
            }
            if (isset( $parent->getPaymentFerien()[0]) && $parent->getPaymentFerien()[0]->getArtString() === 'SEPA'){
                $eltern[$pid]['payment'] = $parent->getPaymentFerien()[0]->getSepa();
            }

            if ($b->getState() === 10 &&isset( $parent->getPaymentFerien()[0]) && $parent->getPaymentFerien()[0]->getArtString() === 'SEPA'){
                $eltern[$pid]['buchungen'][] = $b;
            }
        }

        // Dateiname: ohne Sonderzeichen
        $kursName = $kurs->getTranslations()->get('de')->getTitel();
        $kursName = preg_replace('/[^A-Za-z0-9_-]/', '_', $kursName);

        $filename = sprintf('SEPA_%s_%s.csv', $kursName, date('Y-m-d'));

        $response = new StreamedResponse(function () use ($eltern, $kurs) {
            $out = fopen('php://output', 'w');

            // UTF-8 BOM für Excel (optional aber hilfreich)
            fwrite($out, "\xEF\xBB\xBF");

            $delimiter = ';';

            // Header
            fputcsv($out, [
                'ElternID',
                'Kontoinhaber',
                'IBAN',
                'BIC',
                'Betrag',
                'Waehrung',
                'Name',
                'Vorname',
                'Strasse',
                'Adresszusatz',
                'PLZ',
                'Stadt',
                'Email',
                'Telefon',
                'Verwendungszweck',
                'Kinder',
                'BuchungsIDs'
            ], $delimiter);

            // Optional: Kurs-Titel für Verwendungszweck
            $kursTitel = $kurs->getTranslations()->get('de')->getTitel();


            foreach ($eltern as $pid => $data) {
                /** @var \App\Entity\Stammdaten $p */
                $p = $data['eltern'];
                $buchungen = $data['buchungen'];
                /** @var \App\Entity\PaymentSepa $payment */
                $payment = $data['payment']??null;
                // Summe bilden
                $sum = 0.0;
                $kids = [];
                $bookingIds = [];

                foreach ($buchungen as $b) {
                    $sum += (float)$b->getPreis();
                    $bookingIds[] = (string)$b->getId();

                    $k = $b->getKind();
                    if ($k) {
                        $kids[] = trim(($k->getVorname() ?? '') . ' ' . ($k->getNachname() ?? ''));
                    }
                }

                $kids = array_values(array_unique(array_filter($kids)));
                $kidsStr = implode(', ', $kids);

                $verwendungszweck = 'Betreuung ' . $kursTitel;

                // Optional: Eltern ohne SEPA-Daten trotzdem exportieren oder überspringen?
                // Hier: wir exportieren trotzdem (damit du sie siehst), aber IBAN/BIC leer.
                $iban = $payment->getIban()?? '';
                $bic  = $payment->getBic()?? '';
                $holder = $payment->getKontoinhaber()?? '';

                fputcsv($out, [
                    $p->getId(),
                    $holder,
                    $iban,
                    $bic,
                    number_format(round($sum, 2), 2, ',', ''), // deutsches Format
                    'EUR',
                    (string)($p->getName() ?? ''),
                    (string)($p->getVorname() ?? ''),
                    (string)($p->getStrasse() ?? ''),
                    (string)($p->getAdresszusatz() ?? ''),
                    (string)($p->getPlz() ?? ''),
                    (string)($p->getStadt() ?? ''),
                    (string)($p->getEmail() ?? ''),
                    (string)($p->getPhoneNumber() ?? ''),
                    $verwendungszweck,
                    $kidsStr,
                    implode(',', $bookingIds),
                ], $delimiter);
            }

            fclose($out);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$filename.'"');

        return $response;
    }

    /**
     * @Route("/org_ferien/orders/kinder/{kind_id}", name="ferien_management_kind_details",methods={"GET","POST"})
     */
    public function kindDetails(

        int            $kind_id,

        KindRepository $kindRepo
    )
    {


        $kind = $kindRepo->find($kind_id);
        if (!$kind) {
            throw $this->createNotFoundException('Kind nicht gefunden');
        }

        // Je nach deinem Model: $kind->getParent() / $kind->getErziehungsberechtigter() / etc.
        $parent = $kind->getEltern();
        if (!$parent) {
            throw $this->createNotFoundException('Kein Erziehungsberechtigter zum Kind gefunden');
        }

        // Programme fürs Kind in dieser Org (wie in deinem Code)
        $programme_all = $kind->getKindFerienblocksGebucht();
        $programme = [];
        foreach ($programme_all as $data) {
            if ($data->getFerienblock()->getOrganisation() === $this->getUser()->getOrganisation()) {
                $programme[] = $data;
            }
        }

        return $this->render('ferien_management/childDetails.html.twig', [
            'org' => $this->getUser()->getOrganisation(),
            'kind' => $kind,
            'parent' => $parent,
            'programme' => $programme,
        ]);
    }


    /**
     * @Route("/org_ferien_admin/edit", name="ferien_admin_edit",methods={"GET","POST"})
     */
    public function ferienOrgEdit(Request $request, ValidatorInterface $validator, TranslatorInterface $translator)
    {

        $organisation = $this->managerRegistry->getRepository(Organisation::class)->find($request->get('org_id'));
        if ($organisation->getStadt() != $this->getUser()->getStadt() && $this->getUser()->getOrganisation() != $organisation) {
            throw new \Exception('Wrong City');
        }

        $form = $this->createForm(OrganisationFerienType::class, $organisation);
        $form->handleRequest($request);
        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $organisation = $form->getData();
            $errors = $validator->validate($organisation);
            if (count($errors) == 0) {
                $em = $this->managerRegistry->getManager();
                $em->persist($organisation);
                $em->flush();
                $text = $translator->trans('Erfolgreich geändert');
                return $this->redirectToRoute('ferien_admin_edit', array('snack' => $text, 'org_id' => $organisation->getId()));
            }

        }
        $title = $translator->trans('Ferieneinstellungen ändern');
        return $this->render('administrator/neu.html.twig', array('title' => $title, 'form' => $form->createView(), 'errors' => $errors));

    }

}
