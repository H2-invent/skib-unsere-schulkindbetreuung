<?php

namespace App\Controller;
/**
 * Created by PhpStorm.
 * User: Emanuel
 * Date: 06.09.2019
 * Time: 12:21
 */

use App\Entity\Kind;
use App\Entity\Schule;
use App\Entity\Stadt;
use App\Entity\Stammdaten;
use App\Entity\Zeitblock;
use App\Form\Type\LoerrachEltern;
use App\Form\Type\LoerrachKind;
use App\Form\Type\SepaStammdatenType;
use App\Service\AnmeldeEmailService;
use App\Service\ErrorService;
use App\Service\IcsService;
use App\Service\MailerService;
use App\Service\PrintAGBService;
use App\Service\PrintService;
use App\Service\SchuljahrService;
use App\Service\SchulkindBetreuungAdresseService;
use App\Service\SchulkindBetreuungKindNeuService;
use App\Service\SchulkindBetreuungKindSEPAService;
use App\Service\StamdatenFromCookie;
use App\Service\ToogleKindBlockSchulkind;
use App\Service\WorkflowAbschluss;
use App\Service\WorkflowStart;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Qipsius\TCPDFBundle\Controller\TCPDFController;

class LoerrachWorkflowController extends AbstractController
{
    public $beruflicheSituation;

    public function __construct(TranslatorInterface $translator)
    {
        $this->beruflicheSituation = array(
            $translator->trans('Alleinerziehender Elternteil /Erziehungsberechtigter ist berufstätig') => 1,
            $translator->trans('Alleinerziehender Elternteil / Erziehungsberechtigter ist arbeitssuchend') => 2,
            $translator->trans('Beide Elternteile / Erziehungsberechtigte sind berufstätig') => 3,
            $translator->trans('Beide Elternteile / Erziehungsberechtigte sind arbeitssuchend') => 4,
            $translator->trans('Ein Elternteil / Erziehungsberechtigter ist berufstätig // arbeitssuchend') => 5,

        );

    }

    /**
     * @Route("/{slug}/adresse",name="loerrach_workflow_adresse",methods={"GET","POST"})
     * @ParamConverter("stadt", options={"mapping"={"slug"="slug"}})
     */
    public function adresseAction(ParameterBagInterface            $parameterBag,
                                  ErrorService                     $errorService,
                                  SchulkindBetreuungAdresseService $schulkindBetreuungAdresseService,
                                  AuthorizationCheckerInterface    $authorizationChecker,
                                  TranslatorInterface              $translator,
                                  Stadt                            $stadt,
                                  Request                          $request,
                                  ValidatorInterface               $validator,
                                  StamdatenFromCookie              $stamdatenFromCookie,
                                  SchuljahrService                 $schuljahrService)
    {
        $schuljahr = $schuljahrService->getSchuljahr($stadt);

        if ($schuljahr === null) {
            return $this->redirectToRoute('workflow_closed', array('slug' => $stadt->getSlug()));
        }
        if ($parameterBag->get('wartung') == 'true') {
            return $this->redirectToRoute('workflow_wartung', array('redirect' => $this->generateUrl('loerrach_workflow_adresse', array('slug' => $stadt->getSlug()))));
        }
        $adresse = new Stammdaten();

        if ($stamdatenFromCookie->getStammdatenFromCookie($request)) {
            $adresse = $stamdatenFromCookie->getStammdatenFromCookie($request);
        }
        //Add SecCode into if to create a SecCode the first time to be not "null"
        $adresse = $schulkindBetreuungAdresseService->setUID($adresse);
        $formArr = array('einkommen' => array_flip($stadt->getGehaltsklassen()), 'beruflicheSituation' => $this->beruflicheSituation,'stadt'=>$stadt);

        $form = $this->createForm(LoerrachEltern::class, $adresse, $formArr);
        if (!$authorizationChecker->isGranted('ROLE_ORG_CHILD_CHANGE')) {
            $form->remove('emailDoubleInput');
        }
        $form->handleRequest($request);
        $errors = array();
        $errorsString = array();
        if ($form->isSubmitted()) {
            $adresse = $form->getData();
            if ($authorizationChecker->isGranted('ROLE_ORG_CHILD_CHANGE')) {
                $errors = $validator->validate($adresse, null, ['Default', 'internal']);
            } else {

                $errors = $validator->validate($adresse);

            }

            $errorsString = $errorService->createError($errors, $form);


            if (count($errors) == 0) {
                $schulkindBetreuungAdresseService->setAdress($adresse, $authorizationChecker->isGranted('ROLE_ORG_CHILD_CHANGE'), $request->getClientIp());

                $cookie = new Cookie ('UserID', $adresse->getUid() . "." . hash("sha256", $adresse->getUid() . $this->getParameter("secret")), time() + 60 * 60 * 24 * 365);
                $response = $this->redirectToRoute('workflow_confirm_Email', array('redirect' => $this->generateUrl('loerrach_workflow_schulen', array('slug' => $stadt->getSlug()), UrlGeneratorInterface::ABSOLUTE_URL), 'uid' => $adresse->getUid(), 'stadt' => $stadt->getId()));
                $response->headers->setCookie($cookie);
                return $response;
            }
        }

        $title = $translator->trans('Anmeldeportal Schulkindbetreuung') . '->' . $translator->trans('Adresse') . ' | ' . $stadt->getName();
        return $this->render('workflow/loerrach/adresse.html.twig', array('title' => $title, 'stadt' => $stadt, 'form' => $form->createView(), 'errors' => $errorsString));
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
        $isEdit = false;
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
            $isEdit = true;
        } else {
            $kinder = $adresse->getKinds()->toArray();
        }

        $renderKinder = array();
        foreach ($kinder as $data) {
            $renderKinder[$data->getSchule()->getId()][] = $data;
        }
        return $this->render('workflow/loerrach/schulen.html.twig', array('isEdit' => $isEdit, 'schule' => $schule, 'stadt' => $stadt, 'adresse' => $adresse, 'kinder' => $renderKinder));
    }

    /**
     * @Route("/{slug}/schulen/kind/neu",name="loerrach_workflow_schulen_kind_neu",methods={"GET","POST"})
     * @ParamConverter("stadt", options={"mapping"={"slug"="slug"}})
     */
    public function neukindAction(SchulkindBetreuungKindNeuService $schulkindBetreuungKindNeuService, Stadt $stadt, Request $request, ValidatorInterface $validator, TranslatorInterface $translator, StamdatenFromCookie $stamdatenFromCookie, SchuljahrService $schuljahrService)
    {
        $adresse = new Stammdaten;

        //Include Parents in this route
        if ($stamdatenFromCookie->getStammdatenFromCookie($request)) {
            $adresse = $stamdatenFromCookie->getStammdatenFromCookie($request);
        }
        $schule = $this->getDoctrine()->getRepository(Schule::class)->find($request->get('schule_id'));

        $kind = new Kind();
        $kind = $schulkindBetreuungKindNeuService->prepareKind($kind, $schule, $adresse);
        // Load the data from the city into the controller as $stadt
        $schuljahr = $schuljahrService->getSchuljahr($stadt);
        $form = $this->createForm(LoerrachKind::class, $kind, array('validation_groups' => ['Eltern'], 'action' => $this->generateUrl('loerrach_workflow_schulen_kind_neu', array('slug' => $stadt->getSlug(), 'schule_id' => $schule->getId()))));
        $form = $schulkindBetreuungKindNeuService->cleanUpForm($form, $schuljahr, $schule);
        $kind = $schulkindBetreuungKindNeuService->cleanUpKind($schuljahr, $schule, $kind);
        try {
            $form->handleRequest($request);
        } catch (\Exception $e) {
            $text = $translator->trans('Überprüfe Sie Ihre Eingabe');
            return new JsonResponse(array('snack' => array(array('type' => 'error', 'text' => $text))));
        }


        if ($form->isSubmitted()) {
            try {

                $kind = $form->getData();
                return $schulkindBetreuungKindNeuService->saveKind($kind, $this->isGranted('ROLE_ORG_CHILD_CHANGE'), $stadt, $form);
            } catch (\Exception $e) {
                $text = $translator->trans('Fehler. Bitte versuchen Sie es erneut.');
                return new JsonResponse(array('snack' => array(array('type' => 'error', 'text' => $text))));
            }

        }
        return $this->render('workflow/loerrach/kindForm.html.twig', array('schule' => $schule, 'form' => $form->createView()));
    }

    /**
     * @Route("/{slug}/schulen/kind/edit",name="loerrach_workflow_schulen_kind_edit",methods={"GET","POST"})
     * @ParamConverter("stadt", options={"mapping"={"slug"="slug"}})
     */
    public function editkindAction(SchulkindBetreuungKindNeuService $schulkindBetreuungKindNeuService, Stadt $stadt, Request $request, ValidatorInterface $validator, TranslatorInterface $translator, StamdatenFromCookie $stamdatenFromCookie)
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

        try {
            $form->handleRequest($request);
        } catch (\Exception $e) {
            $text = $translator->trans('Überprüfe Sie Ihre Eingabe');
            return new JsonResponse(array('snack' => array(array('type' => 'error', 'text' => $text))));
        }
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $kind = $form->getData();
                return $schulkindBetreuungKindNeuService->saveKind($kind, $this->isGranted('ROLE_ORG_CHILD_CHANGE'), $stadt, $form);
            } catch (\Exception $e) {
                $text = array($translator->trans('Fehler. Bitte versuchen Sie es erneut.'));
                return new JsonResponse(array('snack' => array(array('type' => 'error', 'text' => $text))));
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
        // When more then one active year is available and an old Kind has to be changed, we need to set the schuljahr back to the original schuljahr of one of the time slots.
        if (count($kind->getRealZeitblocks()) > 0) {
            $schuljahr = $kind->getRealZeitblocks()[0]->getActive();
        } else {
            $schuljahr = $schuljahrService->getSchuljahr($stadt);
        }

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

        return $this->render('workflow/loerrach/blockKinder.html.twig', array('stadt' => $stadt, 'kind' => $kind, 'blocks' => $renderBlocks, 'schuljahr' => $schuljahr));
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
            return $this->redirectToRoute('loerrach_workflow_adresse', array('slug' => $stadt->getSlug()));
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
    public function sepaAction(ErrorService $errorService, SchulkindBetreuungKindSEPAService $schulkindBetreuungKindSEPAService, Request $request, Stadt $stadt, StamdatenFromCookie $stamdatenFromCookie, ValidatorInterface $validator)
    {
        $adresse = new Stammdaten;

        //Include Parents in this route
        if ($stamdatenFromCookie->getStammdatenFromCookie($request)) {
            $adresse = $stamdatenFromCookie->getStammdatenFromCookie($request);
        } else {
            return $this->redirectToRoute('loerrach_workflow_adresse', array('slug' => $stadt->getSlug()));
        }
        $renderOrganisation = $schulkindBetreuungKindSEPAService->findOrg($adresse);
        $form = $this->createForm(SepaStammdatenType::class, $adresse);

        $form->handleRequest($request);
        $errors = array();
        $errorString = array();
        if ($form->isSubmitted()) {
            $adresse = $form->getData();
            $errors = $validator->validate($adresse, null, ['Schulkind']);
            $errorString = $errorService->createError($errors, $form);
            if (count($errors) == 0) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($adresse);
                $em->flush();
                $response = $this->redirectToRoute('loerrach_workflow_zusammenfassung', array('slug' => $stadt->getSlug()));
                return $response;
            }
        }
        return $this->render('workflow/loerrach/bezahlen.html.twig', array('stadt' => $stadt, 'form' => $form->createView(), 'errors' => $errorString, 'organisation' => $renderOrganisation));
    }


    /**
     * @Route("/{slug}/zusammenfassung",name="loerrach_workflow_zusammenfassung",methods={"GET"})
     * @ParamConverter("stadt", options={"mapping"={"slug"="slug"}})
     */
    public function zusammenfassungAction(SchulkindBetreuungKindSEPAService $schulkindBetreuungKindSEPAService, Stadt $stadt, Request $request, StamdatenFromCookie $stamdatenFromCookie, SchuljahrService $schuljahrService)
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
            return $this->redirectToRoute('loerrach_workflow_adresse', array('slug' => $stadt->getSlug()));
        }

        $kind = $adresse->getKinds();
        $preis = 0;
        foreach ($kind as $data) {
            $preis += $data->getPreisforBetreuung();
        }

        $error = false;
        foreach ($kind as $data) {
            if ($data->getTageWithBlocks() < $stadt->getMinDaysperWeek()) {
                $error = true;
                break;
            }
        }
        $renderOrganisation = $schulkindBetreuungKindSEPAService->findOrg($adresse);
        return $this->render('workflow/loerrach/zusammenfassung.html.twig', array(
            'beruflicheSituation' => array_flip($this->beruflicheSituation),
            'kind' => $kind,
            'eltern' => $adresse,
            'stadt' => $stadt,
            'preis' => $preis,
            'error' => $error,
            'organisation' => $renderOrganisation));
    }

    /**
     * @Route("/{slug}/abschluss",name="loerrach_workflow_abschluss",methods={"GET","POST"})
     * @ParamConverter("stadt", options={"mapping"={"slug"="slug"}})
     */
    public function abschlussAction(Request             $request,
                                    ValidatorInterface  $validator,
                                    TranslatorInterface $translator,
                                    MailerService       $mailer,
                                    TCPDFController     $tcpdf,
                                    PrintService        $print,
                                    IcsService          $icsService,
                                    PrintAGBService     $printAGBService,
                                    AnmeldeEmailService $anmeldeEmailService,
                                    StamdatenFromCookie $stamdatenFromCookie,
                                    SchuljahrService    $schuljahrService,
                                    WorkflowAbschluss   $workflowAbschluss,
                                    Stadt               $stadt)
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
            if ($data->getTageWithBlocks() < $stadt->getMinDaysperWeek()) {
                $this->redirectToRoute('loerrach_workflow_zusammenfassung', array('slug' => $stadt->getSlug()));
            }
        }

// Daten speichern und fixieren
        $adresse->setLanguage($request->getLocale());
        $workflowAbschluss->abschluss($adresse, $kind, $stadt);
//Emails an die Eltern senden
        foreach ($kind as $data) {
            $anmeldeEmailService->sendEmail($data, $adresse, $stadt, $translator->trans('Hiermit bestägen wir Ihnen die Anmeldung Ihrers Kindes:'));
            $anmeldeEmailService->setBetreff($translator->trans('Buchungsbestätigung der Schulkindbetreuung für ') . $data->getVorname() . ' ' . $data->getNachname());
            $anmeldeEmailService->send($data, $adresse);
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
