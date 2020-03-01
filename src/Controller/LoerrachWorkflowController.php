<?php

namespace App\Controller;
/**
 * Created by PhpStorm.
 * User: Emanuel
 * Date: 06.09.2019
 * Time: 12:21
 */

use App\Entity\Active;
use App\Entity\Kind;
use App\Entity\Organisation;
use App\Entity\Schule;
use App\Entity\Stadt;
use App\Entity\Stammdaten;
use App\Entity\Zeitblock;
use App\Form\Type\LoerrachEltern;
use App\Form\Type\LoerrachKind;
use App\Form\Type\SepaStammdatenType;
use App\Form\Type\StadtType;
use App\Service\AnmeldeEmailService;
use App\Service\IcsService;
use App\Service\MailerService;
use App\Service\PrintAGBService;
use App\Service\PrintService;
use App\Service\SchuljahrService;
use App\Service\StamdatenFromCookie;
use App\Service\ToogleKindBlockSchulkind;
use App\Service\WorkflowAbschluss;
use App\Service\WorkflowStart;
use Beelab\Recaptcha2Bundle\Form\Type\RecaptchaType;
use Beelab\Recaptcha2Bundle\Validator\Constraints\Recaptcha2;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\Cookie;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\Constraints\Json;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use WhiteOctober\TCPDFBundle\Controller\TCPDFController;


class LoerrachWorkflowController extends AbstractController
{
    private $beruflicheSituation;

    public function __construct(TranslatorInterface $translator)
    {
        $this->beruflicheSituation = array(
            $translator->trans('Beide Elternteile / Erziehungsberechtigte sind berufstätig') => 3,
            $translator->trans('Beide Elternteile / Erziehungsberechtigte sind arbeitssuchend') => 4,
            $translator->trans('Ein Elternteil / Erziehungsberechtigter ist berufstätig // arbeitssuchend') => 5,
            $translator->trans('Alleinerziehender Elternteil /Erziehungsberechtigter ist berufstätig') => 1,
            $translator->trans('Alleinerziehender Elternteil / Erziehungsberechtigter ist arbeitssuchend') => 2,
        );

    }

    /**
     * @Route("/{slug}/adresse",name="loerrach_workflow_adresse",methods={"GET","POST"})
     * @ParamConverter("stadt", options={"mapping"={"slug"="slug"}})
     */
    public function adresseAction(AuthorizationCheckerInterface $authorizationChecker, TranslatorInterface $translator, Stadt $stadt, Request $request, ValidatorInterface $validator, StamdatenFromCookie $stamdatenFromCookie, SchuljahrService $schuljahrService)
    {


        $schuljahr = $schuljahrService->getSchuljahr($stadt);

        if ($schuljahr === null) {
            return $this->redirectToRoute('workflow_closed', array('slug' => $stadt->getSlug()));
        }


        $adresse = new Stammdaten();

        if ($stamdatenFromCookie->getStammdatenFromCookie($request)) {
            $adresse = $stamdatenFromCookie->getStammdatenFromCookie($request);
        }
        //Add SecCode into if to create a SecCode the first time to be not "null"

        if ($adresse->getUid() === null) {
            $adresse->setUid(md5(uniqid('', true)))
                ->setAngemeldet(false);
            $adresse->setCreatedAt(new \DateTime());
        }
        $form = $this->createForm(LoerrachEltern::class, $adresse, array('einkommen' => array_flip($stadt->getGehaltsklassen()), 'beruflicheSituation' => $this->beruflicheSituation));

        $form->handleRequest($request);
        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {

            $adresse = $form->getData();
            $errors = $validator->validate($adresse);
            if (count($errors) == 0) {
                if ($authorizationChecker->isGranted('ROLE_ORG_CHILD_CHANGE')) {
                    $adresse->setEmailConfirmed(true);
                    $adresse->setConfirmEmailSend(true);
                    $adresse->setConfirmationCode(str_shuffle(MD5(microtime())), 0, 6);
                    $adresse->setIpAdresse($request->getClientIp());
                    $adresse->setConfirmDate(new \DateTime());
                }
                $adresse->setFin(false);
                $cookie = new Cookie ('UserID', $adresse->getUid() . "." . hash("sha256", $adresse->getUid() . $this->getParameter("secret")), time() + 60 * 60 * 24 * 365);
                $em = $this->getDoctrine()->getManager();
                $em->persist($adresse);
                $em->flush();
                $response = $this->redirectToRoute('workflow_confirm_Email', array('redirect' => $this->generateUrl('loerrach_workflow_schulen', array('slug' => $stadt->getSlug()), UrlGeneratorInterface::ABSOLUTE_URL), 'uid' => $adresse->getUid(), 'stadt' => $stadt->getId()));
                $response->headers->setCookie($cookie);
                return $response;
            }
        }
        $title = $translator->trans('Anmeldeportal Schulkindbetreuung') . '->' . $translator->trans('Adresse') . ' | ' . $stadt->getName();
        return $this->render('workflow/loerrach/adresse.html.twig', array('title' => $title, 'stadt' => $stadt, 'form' => $form->createView(), 'errors' => $errors));
    }


    /**
     * @Route("/{slug}/schulen",name="loerrach_workflow_schulen",methods={"GET"})
     * @ParamConverter("stadt", options={"mapping"={"slug"="slug"}})
     */
    public function schulenAction(Stadt $stadt, Request $request, StamdatenFromCookie $stamdatenFromCookie, SchuljahrService $schuljahrService)
    {

        // Load all schools from the city into the controller as $schulen
        $schule = $this->getDoctrine()->getRepository(Schule::class)->findBy(array('stadt' => $stadt, 'deleted' => false));
        $schuljahr = $schuljahrService->getSchuljahr($stadt);
        if ($schuljahr === null) {
            return $this->redirectToRoute('workflow_closed', array('slug' => $stadt->getSlug()));
        }

        $adresse = new Stammdaten;
        if ($stamdatenFromCookie->getStammdatenFromCookie($request)) {
            $adresse = $stamdatenFromCookie->getStammdatenFromCookie($request);
        } else {
            return $this->redirectToRoute('loerrach_workflow_adresse', array('slug' => $stadt->getSlug()));
        }
        $kinder = array();
        if ($request->cookies->get('KindID')) {
            $cookie_kind = explode('.', $request->cookies->get('KindID'));
            $kinder = $this->getDoctrine()->getRepository(Kind::class)->findBy(array('id' => $cookie_kind[0]));
        } else {
            $kinder = $adresse->getKinds()->toArray();
        }

        $renderKinder = array();
        foreach ($kinder as $data) {
            $renderKinder[$data->getSchule()->getId()][] = $data;
        }
        return $this->render('workflow/loerrach/schulen.html.twig', array('schule' => $schule, 'stadt' => $stadt, 'adresse' => $adresse, 'kinder' => $renderKinder));
    }

    /**
     * @Route("/{slug}/schulen/kind/neu",name="loerrach_workflow_schulen_kind_neu",methods={"GET","POST"})
     * @ParamConverter("stadt", options={"mapping"={"slug"="slug"}})
     */
    public function neukindAction(Stadt $stadt, Request $request, ValidatorInterface $validator, TranslatorInterface $translator, StamdatenFromCookie $stamdatenFromCookie, SchuljahrService $schuljahrService)
    {
        $adresse = new Stammdaten;

        //Include Parents in this route
        if ($stamdatenFromCookie->getStammdatenFromCookie($request)) {
            $adresse = $stamdatenFromCookie->getStammdatenFromCookie($request);
        }

        $schule = $this->getDoctrine()->getRepository(Schule::class)->find($request->get('schule_id'));

        $kind = new Kind();
        $kind->setEltern($adresse);
        $kind->setSchule($schule);

        // Load the data from the city into the controller as $stadt
        $schuljahr = $schuljahrService->getSchuljahr($stadt);
        $req = array(
            'deleted' => false,
            'active' => $schuljahr,
            'schule' => $schule,
            'ganztag' => 1
        );
        $ganztag = $this->getDoctrine()->getRepository(Zeitblock::class)->findBy($req, array('von' => 'asc'));

        $req = array(
            'deleted' => false,
            'active' => $schuljahr,
            'schule' => $schule,
            'ganztag' => 2
        );
        $halbtag = $this->getDoctrine()->getRepository(Zeitblock::class)->findBy($req, array('von' => 'asc'));

        $form = $this->createForm(LoerrachKind::class, $kind, array('action' => $this->generateUrl('loerrach_workflow_schulen_kind_neu', array('slug' => $stadt->getSlug(), 'schule_id' => $schule->getId()))));
        if (empty($ganztag) && empty($halbtag)) {

        } elseif (empty($ganztag)) {
            $kind->setArt(2);
            $form->remove('art');
        } elseif (empty($halbtag)) {
            $kind->setArt(1);
            $form->remove('art');
        }

        $form->handleRequest($request);
        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $kind = $form->getData();
            $errors = $validator->validate($kind);

            try {
                if (count($errors) == 0) {

                    $em = $this->getDoctrine()->getManager();
                    $em->persist($kind);
                    $em->flush();
                    $text = $translator->trans('Erfolgreich gespeichert');
                    return new JsonResponse(array('error' => 0, 'snack' => $text, 'next' => $this->generateUrl('loerrach_workflow_schulen_kind_zeitblock', array('slug' => $stadt->getSlug(), 'kind_id' => $kind->getId()))));
                }
            } catch (\Exception $e) {
                $text = $translator->trans('Fehler. Bitte versuchen Sie es erneut.');
                return new JsonResponse(array('error' => 1, 'snack' => $text));
            }

        }
        return $this->render('workflow/loerrach/kindForm.html.twig', array('schule' => $schule, 'form' => $form->createView()));
    }

    /**
     * @Route("/{slug}/schulen/kind/edit",name="loerrach_workflow_schulen_kind_edit",methods={"GET","POST"})
     * @ParamConverter("stadt", options={"mapping"={"slug"="slug"}})
     */
    public function editkindAction(Stadt $stadt, Request $request, ValidatorInterface $validator, TranslatorInterface $translator, StamdatenFromCookie $stamdatenFromCookie)
    {
        $adresse = new Stammdaten;

        //Include Parents in this route
        if ($stamdatenFromCookie->getStammdatenFromCookie($request)) {
            $adresse = $stamdatenFromCookie->getStammdatenFromCookie($request);
        }

        $kind = $this->getDoctrine()->getRepository(Kind::class)->findOneBy(array('eltern' => $adresse, 'id' => $request->get('kind_id')));
        $form = $this->createForm(LoerrachKind::class, $kind, array(
            'action' => $this->generateUrl('loerrach_workflow_schulen_kind_edit', array('slug' => $stadt->getSlug(), 'kind_id' => $kind->getId()))
        ));
        $form->remove('art');
        $form->handleRequest($request);
        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $kind = $form->getData();
            $errors = $validator->validate($kind);

            try {
                if (count($errors) == 0) {

                    $em = $this->getDoctrine()->getManager();
                    $em->persist($kind);
                    $em->flush();
                    $text = $translator->trans('Erfolgreich geändert');
                    return new JsonResponse(array('error' => 0, 'snack' => $text));
                }
            } catch (\Exception $e) {
                $text = $translator->trans('Fehler. Bitte versuchen Sie es erneut.');
                return new JsonResponse(array('error' => 1, 'snack' => $text));
            }

        }
        return $this->render('workflow/loerrach/kindForm.html.twig', array('form' => $form->createView()));
    }

    /**
     * @Route("/{slug}/schulen/kind/delete",name="loerrach_workflow_kind_delete",methods={"DELETE"})
     * @ParamConverter("stadt", options={"mapping"={"slug"="slug"}})
     */
    public function deleteAction(Stadt $stadt, Request $request, ValidatorInterface $validator, StamdatenFromCookie $stamdatenFromCookie)
    {
        //Include Parents in this route
        $adresse = new Stammdaten;
        if ($stamdatenFromCookie->getStammdatenFromCookie($request)) {
            $adresse = $stamdatenFromCookie->getStammdatenFromCookie($request);
        }
        $kind = $this->getDoctrine()->getRepository(Kind::class)->findOneBy(array('eltern' => $adresse, 'id' => $request->get('kind_id')));
        $em = $this->getDoctrine()->getManager();
        $em->remove($kind);
        $em->flush();
        return new JsonResponse(array('redirect' => $this->generateUrl('loerrach_workflow_schulen', array('slug' => $stadt->getSlug()))));
    }

    /**
     * @Route("/{slug}/schulen/kind/zeitblock",name="loerrach_workflow_schulen_kind_zeitblock",methods={"GET"})
     * @ParamConverter("stadt", options={"mapping"={"slug"="slug"}})
     */
    public function kindzeitblockAction(Stadt $stadt, Request $request, StamdatenFromCookie $stamdatenFromCookie, SchuljahrService $schuljahrService)
    {

        $adresse = new Stammdaten;

        //Include Parents in this route
        if ($stamdatenFromCookie->getStammdatenFromCookie($request)) {
            $adresse = $stamdatenFromCookie->getStammdatenFromCookie($request);
        }

        $kind = $this->getDoctrine()->getRepository(Kind::class)->findOneBy(array('eltern' => $adresse, 'id' => $request->get('kind_id')));
        $schule = $kind->getSchule();

        // Load the data from the city into the controller as $stadt
        $schuljahr = $schuljahrService->getSchuljahr($stadt);
        $req = array(
            'deleted' => false,
            'active' => $schuljahr,
            'schule' => $schule,
        );
        $block = array();
        if ($kind->getArt() == 1) {
            $req['ganztag'] = 0;
            $block = $this->getDoctrine()->getRepository(Zeitblock::class)->findBy($req, array('von' => 'asc'));
            $req['ganztag'] = $kind->getArt();
            $block = array_merge($block, $this->getDoctrine()->getRepository(Zeitblock::class)->findBy($req, array('von' => 'asc')));

        } elseif ($kind->getArt() == 2) {
            $req['ganztag'] = $kind->getArt();
            $block = $this->getDoctrine()->getRepository(Zeitblock::class)->findBy($req, array('von' => 'asc'));

        }

        $renderBlocks = array();
        foreach ($block as $data) {
            $renderBlocks[$data->getWochentag()][] = $data;
        }

        return $this->render('workflow/loerrach/blockKinder.html.twig', array('stadt' => $stadt, 'kind' => $kind, 'blocks' => $renderBlocks));
    }

    /**
     * @Route("/{slug}/kinder/block/toggle",name="loerrach_workflow_kinder_block_toggle",methods={"PATCH"})
     * @ParamConverter("stadt", options={"mapping"={"slug"="slug"}})
     */
    public function kinderblocktoggleAction(Stadt $stadt, Request $request, ValidatorInterface $validator, TranslatorInterface $translator, StamdatenFromCookie $stamdatenFromCookie, ToogleKindBlockSchulkind $toogleKindBlockSchulkind)
    {


        //Include Parents in this route
        $adresse = new Stammdaten;
        if ($stamdatenFromCookie->getStammdatenFromCookie($request)) {
            $adresse = $stamdatenFromCookie->getStammdatenFromCookie($request);
        }

        $kind = $this->getDoctrine()->getRepository(Kind::class)->findOneBy(array('eltern' => $adresse, 'id' => $request->get('kinder_id')));
        $block = $this->getDoctrine()->getRepository(Zeitblock::class)->find($request->get('block_id'));
        $result = $toogleKindBlockSchulkind->toggleKind($stadt, $kind, $block);
        return new JsonResponse($result);

    }

    /**
     * @Route("/{slug}/mittagessen", name="loerrach_workflow_mittagessen")
     * @ParamConverter("stadt", options={"mapping"={"slug"="slug"}})
     */
    public function mittagessenAction(Request $request, Stadt $stadt, StamdatenFromCookie $stamdatenFromCookie)
    {
        $adresse = new Stammdaten;

        //Include Parents in this route
        if ($stamdatenFromCookie->getStammdatenFromCookie($request)) {
            $adresse = $stamdatenFromCookie->getStammdatenFromCookie($request);
        } else {
            return $this->redirectToRoute('loerrach_workflow_adresse',array('slug'=>$stadt->getSlug()));
        }

        $renderSchulen = array();
        foreach ($adresse->getKinds() as $data) {
            $renderSchulen[$data->getSchule()->getId()] = $data;
        }
        return $this->render('workflow/loerrach/mittagessen.html.twig', array('stadt' => $stadt, 'schule' => $renderSchulen));
    }


    /**
     * @Route("/{slug}/bezahlen", name="loerrach_workflow_bezahlen")
     * @ParamConverter("stadt", options={"mapping"={"slug"="slug"}})
     */
    public function sepaAction(Request $request, Stadt $stadt, StamdatenFromCookie $stamdatenFromCookie, ValidatorInterface $validator)
    {
        $adresse = new Stammdaten;

        //Include Parents in this route
        if ($stamdatenFromCookie->getStammdatenFromCookie($request)) {
            $adresse = $stamdatenFromCookie->getStammdatenFromCookie($request);
        } else {
            return $this->redirectToRoute('loerrach_workflow_adresse',array('slug'=>$stadt->getSlug()));
        }

        $qb = $this->getDoctrine()->getRepository(Organisation::class)->createQueryBuilder('organisation');
        $qb->innerJoin('organisation.schule', 'schule')
            ->innerJoin('schule.kinder', 'kinder')
            ->andWhere('kinder.eltern = :stammdaten')
            ->setParameter('stammdaten', $adresse);
        $query = $qb->getQuery();
        $renderOrganisation = $query->getResult();
        $form = $this->createForm(SepaStammdatenType::class, $adresse);

        $form->handleRequest($request);
        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $adresse = $form->getData();
            $errors = $validator->validate($adresse, null, ['schulkind']);
            if (count($errors) == 0) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($adresse);
                $em->flush();

                $response = $this->redirectToRoute('loerrach_workflow_zusammenfassung', array('slug' => $stadt->getSlug()));
                return $response;
            }

        }
        return $this->render('workflow/loerrach/bezahlen.html.twig', array('stadt' => $stadt, 'form' => $form->createView(), 'errors' => $errors, 'organisation' => $renderOrganisation));
    }


    /**
     * @Route("/{slug}/zusammenfassung",name="loerrach_workflow_zusammenfassung",methods={"GET"})
     * @ParamConverter("stadt", options={"mapping"={"slug"="slug"}})
     */
    public function zusammenfassungAction(Stadt $stadt, Request $request, StamdatenFromCookie $stamdatenFromCookie, SchuljahrService $schuljahrService)
    {
        // Load the data from the city into the controller as $stadt


        //Check for Anmeldung open
        $schuljahr = $schuljahrService->getSchuljahr($stadt);
        if ($schuljahr === null) {
            return $this->redirectToRoute('workflow_closed', array('slug' => $stadt->getSlug()));
        }

        $adresse = new Stammdaten;
        //Include Parents in this route
        if ($stamdatenFromCookie->getStammdatenFromCookie($request)) {
            $adresse = $stamdatenFromCookie->getStammdatenFromCookie($request);
        } else {
            return $this->redirectToRoute('loerrach_workflow_adresse',array('slug'=>$stadt->getSlug()));
        }

        $kind = $adresse->getKinds();
        $preis = 0;
        foreach ($kind as $data) {
            $preis += $data->getPreisforBetreuung();
        }

        $error = false;
        foreach ($kind as $data) {
            if ($data->getTageWithBlocks() < 2) {
                $error = true;
                break;
            }
        }

        return $this->render('workflow/loerrach/zusammenfassung.html.twig', array('beruflicheSituation' => array_flip($this->beruflicheSituation), 'kind' => $kind, 'eltern' => $adresse, 'stadt' => $stadt, 'preis' => $preis, 'error' => $error));
    }

    /**
     * @Route("/{slug}/abschluss",name="loerrach_workflow_abschluss",methods={"GET","POST"})
     * @ParamConverter("stadt", options={"mapping"={"slug"="slug"}})
     */
    public function abschlussAction(Request $request,
                                    ValidatorInterface $validator,
                                    TranslatorInterface $translator,
                                    MailerService $mailer,
                                    TCPDFController $tcpdf,
                                    PrintService $print,
                                    IcsService $icsService,
                                    PrintAGBService $printAGBService,
                                    AnmeldeEmailService $anmeldeEmailService,
                                    StamdatenFromCookie $stamdatenFromCookie,
                                    SchuljahrService $schuljahrService,
                                    WorkflowAbschluss $workflowAbschluss,
                                    Stadt $stadt)
    {

        //Check for Anmeldung open
        $schuljahr = $schuljahrService->getSchuljahr($stadt);
        if ($schuljahr === null) {
            return $this->redirectToRoute('workflow_closed', array('slug' => $stadt->getSlug()));
        }

//Include Parents in this route
        $adresse = new Stammdaten;
        if ($stamdatenFromCookie->getStammdatenFromCookie($request)) {
            $adresse = $stamdatenFromCookie->getStammdatenFromCookie($request);
        } else {
            return $this->redirectToRoute('loerrach_workflow_adresse', array('slug' => $stadt->getSlug()));
        }

        $kind = $adresse->getKinds();
        foreach ($kind as $data) {
            if ($data->getTageWithBlocks() < 2) {
                $this->redirectToRoute('loerrach_workflow_zusammenfassung', array('slug' => $stadt->getSlug()));
            }
        }

// Daten speichern und fixieren
        $adresse->setLanguage($request->getLocale());
       $workflowAbschluss->abschluss($adresse, $kind);
//Emails an die Eltern senden
        foreach ($kind as $data) {
           $anmeldeEmailService->sendEmail($data, $adresse, $stadt, $this->beruflicheSituation);
        }

        $response = $this->render('workflow/abschluss.html.twig', array('kind' => $kind, 'eltern' => $adresse, 'stadt' => $stadt));
        $response->headers->clearCookie('UserID');
        $response->headers->clearCookie('SecID');
        $response->headers->clearCookie('KindID');
        return $response;

    }

    /**
     * @Route("/{slug}/berechnung/einKind",name="loerrach_workflow_preis_einKind",methods={"GET"})
     * @ParamConverter("stadt", options={"mapping"={"slug"="slug"}})
     */
    public
    function berechnungAction(Stadt $stadt, Request $request, ValidatorInterface $validator, TranslatorInterface $translator, StamdatenFromCookie $stamdatenFromCookie)
    {
        // Load the data from the city into the controller as $stadt
        $result = array(
            'error' => 0,
            'text' => $translator->trans('Preis erfolgreich berechnet.'),

        );

        //Include Parents in this route
        $adresse = new Stammdaten;
        if ($stamdatenFromCookie->getStammdatenFromCookie($request)) {
            $adresse = $stamdatenFromCookie->getStammdatenFromCookie($request);
        } else {
            return $this->redirectToRoute('loerrach_workflow_adresse');
        }
        $kind = $this->getDoctrine()->getRepository(Kind::class)->findOneBy(
            array('id' => $request->get('kind_id'), 'eltern' => $adresse)
        );


        // Wenn weniger als zwei Blöcke für das Kind ausgewählt sind

        if ($kind->getTageWithBlocks() < $stadt->getMinDaysperWeek()) {
            $result['error'] = 1;
            $result['text'] = $translator->trans('Bitte weiteres Betreuungszeitfenster auswählen (Es müssen mindestens zwei Tage ausgewählt werden)');
            return new JsonResponse($result);
        }
        $result['betrag'] = number_format($kind->getPreisforBetreuung(), 2, ',', '.');
        return new JsonResponse($result);

    }

    /**
     * @Route("/{slug}/berechnung/printPdf",name="loerrach_workflow_print_pdf",methods={"GET"})
     * @ParamConverter("stadt", options={"mapping"={"slug"="slug"}})
     */
    public
    function prinPdf(Request $request, ValidatorInterface $validator, TranslatorInterface $translator, TCPDFController $tcpdf, PrintService $print, StamdatenFromCookie $stamdatenFromCookie)
    {
        $elter = $stamdatenFromCookie->getStammdatenFromCookie($request);
        $stadt = $elter->getKinds()[0]->getSchule()->getStadt();

        $kind = $this->getDoctrine()->getRepository(Kind::class)->findOneBy(array('eltern' => $elter, 'id' => $request->get(
            'id')));
        $organisation = $kind->getAllBlocks()[0]->getSchule()->getOrganisation();

        $fileName = $kind->getVorname() . '_' . $kind->getNachname() . '_' . $kind->getSchule()->getName();


        return $print->printAnmeldebestaetigung($kind, $elter, $stadt, $tcpdf, $fileName, $this->beruflicheSituation, $organisation, 'D');


    }

    /**
     * @Route("/admin/adresse/bypass",name="loerrach_workflow_bypass",methods={"GET","POST"})
     */
    public
    function bypassAction(Request $request, ValidatorInterface $validator)
    {
        $adresse = $this->getDoctrine()->getRepository(Stammdaten::class)->find($request->get('id'));
        $cookie = new Cookie ('UserID', $adresse->getUid() . "." . hash("sha256", $adresse->getUid() . $this->getParameter("secret")), time() + 60 * 60 * 24 * 365);
        $em = $this->getDoctrine()->getManager();
        $em->persist($adresse);
        $em->flush();
        $response = $this->redirectToRoute('loerrach_workflow_adresse');

        $response->headers->setCookie($cookie);
        return $response;
    }

}
