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
use App\Entity\Schule;
use App\Entity\Stadt;
use App\Entity\Stammdaten;
use App\Entity\Zeitblock;
use App\Form\Type\LoerrachEltern;
use App\Form\Type\LoerrachKind;
use App\Form\Type\SepaStammdatenType;
use App\Service\AnmeldeEmailService;
use App\Service\BerechnungsService;
use App\Service\ElternService;
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
use Doctrine\Persistence\ManagerRegistry;
use phpDocumentor\Reflection\DocBlock;
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

    public function __construct(TranslatorInterface $translator, private ManagerRegistry $managerRegistry)
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
        $formArr = array('einkommen' => array_flip($stadt->getGehaltsklassen()), 'beruflicheSituation' => $this->beruflicheSituation, 'stadt' => $stadt);

        $form = $this->createForm(LoerrachEltern::class, $adresse, $formArr);
        if (!$authorizationChecker->isGranted('ROLE_ORG_CHILD_CHANGE')) {
            $form->remove('emailDoubleInput');
        }
        if ($request->cookies->get('KindID')) {
            return $this->redirectToRoute('loerrach_workflow_schulen', array('slug' => $stadt->getSlug()));
        }
        $form->handleRequest($request);
        $errorsString = array();
        if ($form->isSubmitted()) {
            $adresse = $form->getData();
            $adresse->setStartDate($schuljahr->getVon());
            if (!$adresse->getTracing()) {
                $adresse->setTracing(md5(uniqid('stammdaten', true)));
            }
            if ($authorizationChecker->isGranted('ROLE_ORG_CHILD_CHANGE')) {
                $errors = $validator->validate($adresse, null, ['Default', 'internal']);
            } else {

                $errors = $validator->validate($adresse, null, ['Default']);
                foreach ($adresse->getGeschwisters() as $data) {
                    $tmpErr = $validator->validate($data, null, ['Default']);

                    $errors->addAll($tmpErr);

                }
                foreach ($adresse->getPersonenberechtigters() as $data2) {
                    $tmpErr = $validator->validate($data2, null, ['Default']);

                    $errors->addAll($tmpErr);

                }
            }

            $errorsString = $errorService->createError($errors, $form);


            if (count($errors) == 0) {
                $schulkindBetreuungAdresseService->setAdress($adresse, $authorizationChecker->isGranted('ROLE_ORG_CHILD_CHANGE'), $request->getClientIp());

                $cookie = new Cookie ('UserID', $adresse->getUid() . "." . hash("sha256", $adresse->getUid() . $this->getParameter("secret")), time() + 60 * 60 * 24 * 10);
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
        $schule = $this->managerRegistry->getRepository(Schule::class)->findBy(array('stadt' => $stadt, 'deleted' => false));
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
            $kinder = $this->managerRegistry->getRepository(Kind::class)->findBy(array('id' => $cookie_kind[0]));
            $isEdit = true;
        } else {
            $kinder = $adresse->getKinds()->toArray();
        }

        $renderKinder = array();
        foreach ($kinder as $data) {
            $schuljahr = $this->managerRegistry->getRepository(Active::class)->findSchuljahrFromKind($data);
            if (($isEdit && !$data->getStartDate()) || ($isEdit && !$stadt->getSettingsSkibShowSetStartDateOnChange())) {
                if (new \DateTime() < $schuljahr->getVon()){
                    $data->setStartDate($schuljahr->getVon());
                }else{
                    $data->setStartDate((new \DateTime())->modify($stadt->getSettingSkibDefaultNextChange()));
                }
                $em = $this->managerRegistry->getManager();
                $em->persist($data);
                $em->flush();
            }
            $renderKinder[$data->getSchule()->getId()][] = $data;
        }
        return $this->render('workflow/loerrach/schulen.html.twig', array('isEdit' => $isEdit, 'schule' => $schule, 'stadt' => $stadt, 'adresse' => $adresse, 'kinder' => $renderKinder));
    }

    /**
     * @Route("/{slug}/schulen/kind/neu",name="loerrach_workflow_schulen_kind_neu",methods={"GET","POST"})
     * @ParamConverter("stadt", options={"mapping"={"slug"="slug"}})
     */
    public function neukindAction(SchulkindBetreuungKindNeuService $schulkindBetreuungKindNeuService, Stadt $stadt, Request $request, ValidatorInterface $validator, TranslatorInterface $translator, StamdatenFromCookie $stamdatenFromCookie, SchuljahrService $schuljahrService, ParameterBagInterface $parameterBag)
    {
        $adresse = new Stammdaten;

        //Include Parents in this route
        if ($stamdatenFromCookie->getStammdatenFromCookie($request)) {
            $adresse = $stamdatenFromCookie->getStammdatenFromCookie($request);
        }
        $schule = $this->managerRegistry->getRepository(Schule::class)->find($request->get('schule_id'));

        $kind = new Kind();


        // Load the data from the city into the controller as $stadt
        $schuljahr = $schuljahrService->getSchuljahr($stadt);
        $kind = $schulkindBetreuungKindNeuService->prepareKind($kind, $schule, $adresse, $schuljahr);
        $form = $this->createForm(LoerrachKind::class, $kind, array('schuljahr' => $schuljahr, 'validation_groups' => ['Eltern'], 'action' => $this->generateUrl('loerrach_workflow_schulen_kind_neu', array('slug' => $stadt->getSlug(), 'schule_id' => $schule->getId()))));
        if ($this->getUser() && $this->getUser()->getOrganisation()->getStadt() === $stadt && in_array('ROLE_ORG_CHILD_CHANGE', $this->getUser()->getRoles(), true) && $stadt->getSettingsSkibShowSetStartDateOnChange()) {

        } else {
            $form->remove('startDate');
        }
        $schuljahr = $schuljahrService->getSchuljahr($stadt);
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
    public function editkindAction(SchulkindBetreuungKindNeuService $schulkindBetreuungKindNeuService, Stadt $stadt, Request $request, ValidatorInterface $validator, TranslatorInterface $translator, StamdatenFromCookie $stamdatenFromCookie, SchuljahrService $schuljahrService)
    {
        $adresse = new Stammdaten;

        //Include Parents in this route
        if ($stamdatenFromCookie->getStammdatenFromCookie($request)) {
            $adresse = $stamdatenFromCookie->getStammdatenFromCookie($request);
        }
        $kind = $this->managerRegistry->getRepository(Kind::class)->findOneBy(array('eltern' => $adresse, 'id' => $request->get('kind_id')));
        $isEdit = false;
        $schuljahr = $schuljahrService->getSchuljahr($stadt);
        if ($request->cookies->get('KindID')) {
            $isEdit = true;
            $schuljahr = $this->managerRegistry->getRepository(Active::class)->findSchuljahrFromKind($kind);
        }



        $form = $this->createForm(LoerrachKind::class, $kind, array(
            'schuljahr' => $schuljahr, 'action' => $this->generateUrl('loerrach_workflow_schulen_kind_edit', array('slug' => $stadt->getSlug(), 'kind_id' => $kind->getId()))
        ));
        $form->remove('art');
        if ($this->getUser() && in_array('ROLE_ORG_CHILD_CHANGE', $this->getUser()->getRoles(), true) && $stadt->getSettingsSkibShowSetStartDateOnChange()) {

        } else {
            $form->remove('startDate');
        }
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
        $kind = $this->managerRegistry->getRepository(Kind::class)->findOneBy(array('eltern' => $adresse, 'id' => $request->get('kind_id')));
        $em = $this->managerRegistry->getManager();
        $em->remove($kind);
        $em->flush();
        return new JsonResponse(array('redirect' => $this->generateUrl('loerrach_workflow_schulen', array('slug' => $stadt->getSlug()))));
    }

    /**
     * @Route("/org_child/change/schulen/kind/startDate",name="loerrach_workflow_schulen_kind_startDate",methods={"POST"})
     */
    public function kindStartDateAction(Request $request, StamdatenFromCookie $stamdatenFromCookie)
    {
        $adresse = new Stammdaten;

        //Include Parents in this route
        if ($stamdatenFromCookie->getStammdatenFromCookie($request)) {
            $adresse = $stamdatenFromCookie->getStammdatenFromCookie($request);
        }

        $kind = $this->managerRegistry->getRepository(Kind::class)->findOneBy(array('eltern' => $adresse, 'id' => $request->get('kind_id')));
        $kind->setStartDate(new \DateTime($request->get('startDate')));
        $em = $this->managerRegistry->getManager();
        $em->persist($kind);
        $em->flush();
        return new JsonResponse(array('error' => false));
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

        $kind = $this->managerRegistry->getRepository(Kind::class)->findOneBy(array('eltern' => $adresse, 'id' => $request->get('kind_id')));
        $schule = $kind->getSchule();

        // Load the data from the city into the controller as $stadt
        // When more then one active year is available and an old Kind has to be changed, we need to set the schuljahr back to the original schuljahr of one of the time slots.
        if (count($kind->getRealZeitblocks()) > 0) {
            $schuljahr = $this->managerRegistry->getRepository(Active::class)->findSchuljahrFromKind($kind);
        } else {
            $schuljahr = $schuljahrService->getSchuljahr(stadt: $stadt);// $this->managerRegistry->getRepository(Active::class)->findSchuljahrfromStadtAndStichtag($stadt,$kind->getStartDate());
        }

        $req = array(
            'deleted' => false,
            'active' => $schuljahr,
            'schule' => $schule,
        );
        $block = array();
        if ($kind->getArt() == 1) {
            $req['ganztag'] = 0;
            $block = $this->managerRegistry->getRepository(Zeitblock::class)->findBy($req, array('von' => 'asc'));
            $req['ganztag'] = $kind->getArt();
            $block = array_merge($block, $this->managerRegistry->getRepository(Zeitblock::class)->findBy($req, array('von' => 'asc')));

        } elseif ($kind->getArt() == 2) {
            $req['ganztag'] = $kind->getArt();
            $block = $this->managerRegistry->getRepository(Zeitblock::class)->findBy($req, array('von' => 'asc'));

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

        $kind = $this->managerRegistry->getRepository(Kind::class)->findOneBy(array('eltern' => $adresse, 'id' => $request->get('kinder_id')));
        $block = $this->managerRegistry->getRepository(Zeitblock::class)->find($request->get('block_id'));
        if ($block->getDeaktiviert()) {
            return new JsonResponse(array('error' => 1, 'snack' => 'Error, this action is not allowed'));
        }

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
        if ($stadt->getSkibSettingsBypassBankdaten()){
            $response = $this->redirectToRoute('loerrach_workflow_zusammenfassung', array('slug' => $stadt->getSlug()));
            return $response;
        }
        $renderOrganisation = $schulkindBetreuungKindSEPAService->findOrg($adresse);
        $form = $this->createForm(SepaStammdatenType::class, $adresse, ['stadt' => $stadt]);

        $form->handleRequest($request);
        $errors = array();
        $errorString = array();
        if ($form->isSubmitted()) {
            $adresse = $form->getData();
            $valdation = [$stadt->getSettingsSkibSepaElektronisch() ? 'SchulkindSepa' : ',Schulkind'];
            $errors = $validator->validate($adresse, null, $valdation);
            $errorString = $errorService->createError($errors, $form);
            if (count($errors) == 0) {
                $em = $this->managerRegistry->getManager();
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
    public function zusammenfassungAction(
        SchulkindBetreuungKindSEPAService $schulkindBetreuungKindSEPAService,
        Stadt                             $stadt,
        Request                           $request,
        StamdatenFromCookie               $stamdatenFromCookie,
        SchuljahrService                  $schuljahrService,
        BerechnungsService                $berechnungsService)
    {
        // Load the data from the city into the controller as $stadt

        set_time_limit(300);
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
            $preis += $berechnungsService->getPreisforBetreuung($data, true, $data->getStartDate(), true);
        }
        $isEdit = false;
        if ($request->cookies->get('KindID')) {
            $isEdit = true;
        }
        $error = false;
        foreach ($kind as $data) {
            if ($data->getTageWithBlocks() < $stadt->getMinDaysperWeek() && !$isEdit) {
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
            'noPrintout' => true,
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
                                    Stadt               $stadt,
                                    ElternService       $elternService)
    {

        //Check for Anmeldung open
        set_time_limit(600);
        $schuljahr = $schuljahrService->getSchuljahr($stadt);
        if (!$schuljahr) {
            return $this->redirectToRoute('workflow_closed', array('slug' => $stadt->getSlug()));
        }

//Include Parents in this route

        $adresse = $stamdatenFromCookie->getStammdatenFromCookie($request);
        if (!$adresse) {
            return $this->redirectToRoute('loerrach_workflow_adresse', array('slug' => $stadt->getSlug()));
        }

        $kindeToEdit = null;
        if ($request->cookies->get('KindID')) {
            $cookie_kind = explode('.', $request->cookies->get('KindID'));
            $kindeToEdit = $this->managerRegistry->getRepository(Kind::class)->findOneBy(array('id' => $cookie_kind[0]));
            $isEdit = true;
        }
        $kinder = $adresse->getKinds();

        foreach ($kinder as $data) {
            if ($data->getTageWithBlocks() < $stadt->getMinDaysperWeek() && !$isEdit) {
                $this->redirectToRoute('loerrach_workflow_zusammenfassung', array('slug' => $stadt->getSlug()));
            }
        }


// Daten speichern und fixieren
        $adresse->setLanguage($request->getLocale());
        $workflowAbschluss->abschluss($adresse, $stadt, $kindeToEdit);
        $kinder = $adresse->getKinds();
//Emails an die Eltern senden

        foreach ($kinder as $data) {
            $adresse = $elternService->getElternForSpecificTimeAndKind($data, $data->getStartDate());
            $anmeldeEmailService->sendEmail($data, $adresse, $stadt, $translator->trans('Hiermit bestägen wir Ihnen die Anmeldung Ihres Kindes:'));
            $anmeldeEmailService->send($data, $adresse);
        }

        $response = $this->render('workflow/abschluss.html.twig', array('kind' => $kinder, 'eltern' => $adresse, 'stadt' => $stadt));
        $response->headers->clearCookie('UserID');
        $response->headers->clearCookie('SecID');
        $response->headers->clearCookie('KindID');
        $request->getSession()->remove('schuljahr_to_add');
        return $response;

    }

    /**
     * @Route("/{slug}/berechnung/einKind",name="loerrach_workflow_preis_einKind",methods={"GET"})
     * @ParamConverter("stadt", options={"mapping"={"slug"="slug"}})
     */
    public
    function berechnungAction(Stadt $stadt, Request $request, ValidatorInterface $validator, TranslatorInterface $translator, StamdatenFromCookie $stamdatenFromCookie, BerechnungsService $berechnungsService)
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
        $kind = $this->managerRegistry->getRepository(Kind::class)->findOneBy(
            array('id' => $request->get('kind_id'), 'eltern' => $adresse)
        );


        // Wenn weniger als zwei Blöcke für das Kind ausgewählt sind

        if ($kind->getTageWithBlocks() < $stadt->getMinDaysperWeek()) {
            $result['error'] = 1;
            $result['text'] = $translator->trans('Bitte weiteres Betreuungszeitfenster auswählen (Es müssen mindestens zwei Tage ausgewählt werden)');
            return new JsonResponse($result);
        }
        $result['betrag'] = number_format($berechnungsService->getPreisforBetreuung($kind, true, $kind->getStartDate(), true), 2, ',', '.');
        return new JsonResponse($result);

    }

    /**
     * @Route("/{slug}/berechnung/printPdf",name="loerrach_workflow_print_pdf",methods={"GET"})
     * @ParamConverter("stadt", options={"mapping"={"slug"="slug"}})
     */
    public
    function prinPdf(Request $request, ValidatorInterface $validator, TranslatorInterface $translator, PrintService $print, StamdatenFromCookie $stamdatenFromCookie)
    {
        $elter = $stamdatenFromCookie->getStammdatenFromCookie($request);
        $stadt = $elter->getKinds()[0]->getSchule()->getStadt();

        $kind = $this->managerRegistry->getRepository(Kind::class)->findOneBy(array('eltern' => $elter, 'id' => $request->get(
            'id')));
        $organisation = $kind->getAllBlocks()[0]->getSchule()->getOrganisation();

        $fileName = $kind->getVorname() . '_' . $kind->getNachname() . '_' . $kind->getSchule()->getName();


        return $print->printAnmeldebestaetigung($kind, $elter, $stadt, $fileName, $this->beruflicheSituation, $organisation, 'D');


    }

    /**
     * @Route("/admin/adresse/bypass",name="loerrach_workflow_bypass",methods={"GET","POST"})
     */
    public
    function bypassAction(Request $request, ValidatorInterface $validator)
    {
        $adresse = $this->managerRegistry->getRepository(Stammdaten::class)->find($request->get('id'));
        $cookie = new Cookie ('UserID', $adresse->getUid() . "." . hash("sha256", $adresse->getUid() . $this->getParameter("secret")), time() + 60 * 60 * 24 * 365);
        $em = $this->managerRegistry->getManager();
        $em->persist($adresse);
        $em->flush();
        $response = $this->redirectToRoute('loerrach_workflow_adresse');

        $response->headers->setCookie($cookie);
        return $response;
    }

}
