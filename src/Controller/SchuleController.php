<?php

namespace App\Controller;

use App\Entity\Organisation;
use App\Entity\Schule;
use App\Entity\Stadt;
use App\Form\Type\SchuleType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class SchuleController extends AbstractController
{
    public function __construct(
        private ManagerRegistry $managerRegistry,
    ) {
    }

    #[Route(path: '/city_schule/show', name: 'city_admin_schule_show', methods: ['GET'])]
    public function index(Request $request)
    {
        $city = $this->managerRegistry->getRepository(Stadt::class)->findOneBy(['id' => $request->get('id'), 'deleted' => false]);
        if ($city != $this->getUser()->getStadt()) {
            throw new \Exception('Wrong City');
        }
        $schools = $this->managerRegistry->getRepository(Schule::class)->findBy(['stadt' => $city, 'deleted' => false]);

        return $this->render('cityAdminSchule/schulen.html.twig', [
            'schulen' => $schools,
            'city' => $this->getUser()->getStadt(),
        ]);
    }

    #[Route(path: '/city_schule/new', name: 'city_admin_schule_new', methods: ['GET', 'POST'])]
    public function newSchool(Request $request, ValidatorInterface $validator, TranslatorInterface $translator)
    {
        $city = $this->managerRegistry->getRepository(Stadt::class)->find($request->get('id'));
        $organisations = $this->managerRegistry->getRepository(Organisation::class)->findBy(['stadt' => $city]);
        if ($city != $this->getUser()->getStadt()) {
            throw new \Exception('Wrong City');
        }
        $school = new Schule();

        $form = $this->createForm(SchuleType::class, $school, ['organisations' => $organisations]);
        $form->handleRequest($request);
        $errors = [];
        if ($form->isSubmitted() && $form->isValid()) {
            $school = $form->getData();
            $school->setStadt($city);
            $errors = $validator->validate($school);
            if (count($errors) == 0) {
                $em = $this->managerRegistry->getManager();
                $em->persist($school);
                $em->flush();
                $text = $translator->trans('Erfolgreich angelegt');

                return $this->redirectToRoute('city_admin_schule_show', ['snack' => $text, 'id' => $city->getId()]);
            }
        }

        $title = $translator->trans('Schule anlegen');

        return $this->render('administrator/neu.html.twig', ['title' => $title, 'stadt' => $city, 'form' => $form->createView(), 'errors' => $errors]);
    }

    #[Route(path: '/org_shool/edit', name: 'city_admin_schule_edit', methods: ['GET', 'POST'])]
    public function editSchool(Request $request, ValidatorInterface $validator, TranslatorInterface $translator)
    {
        $school = $this->managerRegistry->getRepository(Schule::class)->find($request->get('id'));
        $city = $school->getStadt();
        $organisations = $this->managerRegistry->getRepository(Organisation::class)->findBy(['stadt' => $city]);

        if ($city != $this->getUser()->getStadt()) {
            throw new \Exception('Wrong City');
        }

        $form = $this->createForm(SchuleType::class, $school, ['organisations' => $organisations]);
        $form->handleRequest($request);
        $errors = [];
        if ($form->isSubmitted() && $form->isValid()) {
            $school = $form->getData();
            $errors = $validator->validate($school);
            if (count($errors) == 0) {
                $em = $this->managerRegistry->getManager();
                $em->persist($school);
                $em->flush();
                $text = $translator->trans('Erfolgreich geändert');

                return $this->redirectToRoute('city_admin_schule_detail', ['snack' => $text, 'id' => $school->getId()]);
            }
        }
        $title = $translator->trans('Schule anlegen');

        return $this->render('administrator/neu.html.twig', ['title' => $title, 'stadt' => $city, 'form' => $form->createView(), 'errors' => $errors]);
    }

    #[Route(path: '/city_schule/delete', name: 'city_admin_schule_delete', methods: ['DELETE'])]
    public function deleteSchool(Request $request, ValidatorInterface $validator, TranslatorInterface $translator)
    {
        $school = $this->managerRegistry->getRepository(Schule::class)->find($request->get('id'));
        $city = $school->getStadt();
        if ($city != $this->getUser()->getStadt()) {
            throw new \Exception('Wrong City');
        }

        $school->setDeleted(true);
        $em = $this->managerRegistry->getManager();
        $em->persist($school);
        $em->flush();

        return new JsonResponse(['redirect' => $this->generateUrl('city_admin_schule_show', ['id' => $city->getId()])]);
    }

    #[Route(path: '/org_shool/detail', name: 'city_admin_schule_detail', methods: ['GET'])]
    public function detailSchool(Request $request, ValidatorInterface $validator, TranslatorInterface $translator)
    {
        $school = $this->managerRegistry->getRepository(Schule::class)->find($request->get('id'));
        $city = $school->getStadt();
        if ($city != $this->getUser()->getStadt()) {
            throw new \Exception('Wrong City');
        }

        return $this->render('cityAdminSchule/schulenDetail.html.twig', ['stadt' => $city, 'schule' => $school]);
    }
}
