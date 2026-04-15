<?php

namespace App\Controller;

use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Stadt;
use App\Form\Type\FormelType;
use App\Form\Type\StadtType;
use App\Repository\KindRepository;
use JsonException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\SyntaxError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class StadtverwaltungController extends AbstractController
{
    public function __construct(private ManagerRegistry $managerRegistry)
    {
    }
    #[Route(path: '/admin/index', name: 'admin_index', methods: ['GET'])]
    public function index()
    {
        return $this->render('administrator/index.html.twig', [
            'controller_name' => 'AdministratorController',
        ]);
    }

    #[Route(path: '/admin/stadtverwaltung', name: 'admin_stadt', methods: ['GET'])]
    public function stadtverwaltung()
    {
        $city = $this->managerRegistry->getRepository(Stadt::class)->findBy(array('deleted' => false));

        return $this->render('administrator/stadt.html.twig', [
            'city' => $city
        ]);
    }

    #[Route(path: '/admin/stadtverwaltung/neu', name: 'admin_stadt_neu', methods: ['GET', 'POST'])]
    public function newStadt(Request $request, TranslatorInterface $translator, ValidatorInterface $validator, KindRepository $kindRepository)
    {
        $city = new Stadt();

        if($city->getGehaltsklassen() === null || $city->getGehaltsklassen() === null || sizeof($city->getGehaltsklassen()) != $city->getPreiskategorien()){
            $city->setGehaltsklassen(array_fill(0,$city->getPreiskategorien(), ''));
        }
        $form = $this->createForm(StadtType::class, $city);

        $form->handleRequest($request);
        $errors = array();
        $em = $this->managerRegistry->getManager();
        if ($form->isSubmitted() && $form->isValid()) {
            $city = $form->getData();
            $city->setCreatedAt(new \DateTime());
            $errors = $validator->validate($city);
            if (count($errors) == 0) {
                $em->persist($city);
                $em->flush();
                return $this->redirectToRoute('admin_stadt');
            }

        }
        $title = $translator->trans('Stadt anlegen');

        $kind = $kindRepository->findSampleKind();
        $em->initializeObject($kind);

        return $this->render('administrator/stadtForm.html.twig',
            [
                'title' => $title, 'stadt' => $city, 'form' => $form->createView(),
                'errors' => $errors, 'kind' => $kind, 'eltern' => $kind?->getEltern(),
                'schule' => $kind?->getSchule(), 'organisation' => $kind?->getSchule()?->getOrganisation(),
            ]
        );
    }

    #[Route(path: '/city_edit/stadtverwaltung/edit', name: 'admin_stadt_edit', methods: ['GET', 'POST'])]
    public function editStadt(Request $request, TranslatorInterface $translator, ValidatorInterface $validator, KindRepository $kindRepository)
    {
        $city = $this->managerRegistry->getRepository(Stadt::class)->find($request->get('id'));

        if($city->getGehaltsklassen() === null || $city->getGehaltsklassen() === null || sizeof($city->getGehaltsklassen()) != $city->getPreiskategorien()){
            $city->setGehaltsklassen(array_fill(0,$city->getPreiskategorien(), ''));
        }

        $form = $this->createForm(StadtType::class, $city);
        $form->remove('slug');
        if (!$this->getUser()->hasRole('ROLE_ADMIN')){
           $form->remove('schulkindBetreung');
           $form->remove('ferienprogramm');
            $form->remove('active');
            $form->remove('settingEncryptEmailAttachment');
       }

        $form->handleRequest($request);
        $errors = array();
        $em = $this->managerRegistry->getManager();
        if ($form->isSubmitted() && $form->isValid()) {
            $city = $form->getData();
            $errors = $validator->validate($city);
            if (count($errors) == 0) {
                $em->persist($city);

                //wichtig vor dem Flush
                $city->mergeNewTranslations();
                $em->flush();
                return $this->redirectToRoute('admin_stadt_edit', array('id' => $city->getId(), 'snack' => 'Erfolgreich gespeichert'));
            }

        }

        $title = $translator->trans('Stadt bearbeiten');
        $kind = $kindRepository->findSampleKind();
        $em->initializeObject($kind);

        return $this->render('administrator/stadtForm.html.twig',
            [
                'title' => $title, 'stadt' => $city, 'form' => $form->createView(),
                'errors' => $errors, 'kind' => $kind, 'eltern' => $kind?->getEltern(),
                'schule' => $kind?->getSchule(), 'organisation' => $kind?->getSchule()?->getOrganisation(),
            ]
        );
    }

    #[Route(path: '/admin/stadtverwaltung/delete', name: 'admin_stadt_delete', methods: ['GET'])]
    public function deleteStadt(Request $request)
    {
        $city = $this->managerRegistry->getRepository(Stadt::class)->find($request->get('id'));
        $city->setDeleted(true);
        $em = $this->managerRegistry->getManager();
        $em->persist($city);
        $em->flush();
        return $this->redirectToRoute('admin_stadt');
    }

    #[Route(path: '/admin/berechner', name: 'admin_berechner', methods: ['GET', 'POST'])]
    public function berechnerEdit(Request $request)
    {
        $city = $this->managerRegistry->getRepository(Stadt::class)->find($request->get('id'));
        $form = $this->createForm(FormelType::class, $city);
        $form->handleRequest($request);
        $error = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $city = $form->getData();

            $em = $this->managerRegistry->getManager();
            $em->persist($city);
            $em->flush();
            return $this->redirectToRoute('admin_berechner', array('id' => $city->getId()));
        }


        return $this->render('administrator/neu.html.twig', [
            'form' =>$form->createView(),
            'title'=>'Berechnungsformel',
            'errors'=>$error
        ]);
    }

    #[Route(path: '/admin/formula-test', name: 'admin_formula_test', methods: ['POST'])]
    public function formulaTest(
        Request $request,
        ExpressionLanguage $expressionLanguage,
        KindRepository $kindRepository
    ): JsonResponse
    {
        try {
            $json = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
            $formula = $json['formula'] ?? throw new JsonException();
        } catch (JsonException) {
            return new JsonResponse([], 400);
        }

        $kind = $kindRepository->findSampleKind();
        if ($kind === null) {
            return new JsonResponse([], 500);
        }

        try {
            $weight = $expressionLanguage->evaluate($formula, [
                'kind' => $kind,
                'eltern' => $kind->getEltern(),
                'schule' => $kind->getSchule(),
                'organisation' => $kind->getSchule()?->getOrganisation(),
            ]);
        } catch (SyntaxError) {
            return new JsonResponse([], 400);
        }

        return new JsonResponse(['weight' => $weight]);
    }
}
