<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Active;
use App\Form\Type\SchuljahrType;
use App\Repository\ActiveRepository;
use App\Repository\StadtRepository;
use App\Service\DeleteSchoolYearChildrenService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\SwitchUserToken;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class SchuljahrController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly Security $security,
        private readonly ActiveRepository $activeRepository,
        private readonly StadtRepository $stadtRepository,
        private readonly DeleteSchoolYearChildrenService $deleteSchoolYearChildrenService,
    ) {
    }

    #[Route('city_admin/stadtschuljahr/show', name: 'city_admin_schuljahr_anzeige')]
    public function index(Request $request): Response
    {
        $stadt = $this->stadtRepository->find($request->query->get('id'));
        if ($stadt === null || $stadt !== $this->getUser()?->getStadt()) {
            throw $this->createNotFoundException();
        }

        return $this->render('schuljahr/schuljahre.html.twig', [
            'city' => $stadt,
            'schuljahre' => $this->activeRepository->findBy(['stadt' => $stadt]),
        ]);
    }

    #[Route('city_admin/stadtschuljahr/neu', name: 'city_admin_schuljahr_neu')]
    public function neu(
        Request $request,
        ValidatorInterface $validator,
        TranslatorInterface $translator,
    ): Response {
        $stadt = $this->stadtRepository->find($request->query->get('id'));
        if ($stadt === null || $stadt !== $this->getUser()?->getStadt()) {
            throw $this->createNotFoundException();
        }

        $activity = (new Active())->setStadt($stadt);
        $form = $this->createForm(SchuljahrType::class, $activity, $this->getFormOptions());
        $form->handleRequest($request);

        $errors = null;
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Active $activity */
            $activity = $form->getData();
            $errors = $validator->validate($activity);

            if ($errors->count() === 0) {
                $activity->setAnmeldeEnde($activity->getAnmeldeEnde()->setTime(23, 59, 59));
                $this->entityManager->persist($activity);
                $this->entityManager->flush();

                return $this->redirectToRoute('city_admin_schuljahr_anzeige', [
                    'id' => $stadt->getId(),
                    'snack' => $translator->trans('Erfolgreich angelegt'),
                ]);
            }
        }

        return $this->render('administrator/neu.html.twig', [
            'title' => $translator->trans('Schuljahr anlegen'),
            'form' => $form->createView(),
            'errors' => $errors ?? [],
        ]);
    }

    #[Route('city_admin/stadtschuljahr/edit', name: 'city_admin_schuljahr_edit')]
    public function edit(
        Request $request,
        ValidatorInterface $validator,
        TranslatorInterface $translator,
    ): Response {
        $activity = $this->activeRepository->find($request->query->get('id'));
        if ($activity === null || $activity->getStadt() !== $this->getUser()?->getStadt()) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(SchuljahrType::class, $activity, $this->getFormOptions());
        $form->handleRequest($request);

        $errors = null;
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Active $activity */
            $activity = $form->getData();
            $errors = $validator->validate($activity);

            if ($errors->count() === 0) {
                $activity->setAnmeldeEnde($activity->getAnmeldeEnde()->setTime(23, 59, 59));
                $this->entityManager->persist($activity);
                $this->entityManager->flush();

                return $this->redirectToRoute('city_admin_schuljahr_anzeige', [
                    'id' => $activity->getStadt()->getId(),
                    'snack' => $translator->trans('Erfolgreich geändert'),
                ]);
            }
        }

        return $this->render('administrator/neu.html.twig', [
            'title' => $translator->trans('Schuljahr bearbeiten'),
            'form' => $form->createView(),
            'errors' => $errors ?? [],
        ]);
    }

    #[Route('city_admin/stadtschuljahr/delete', name: 'city_admin_schuljahr_delete')]
    public function delete(Request $request, TranslatorInterface $translator): Response
    {
        $activity = $this->activeRepository->find($request->query->get('id'));
        if ($activity === null || $activity->getStadt() !== $this->getUser()?->getStadt()) {
            throw $this->createNotFoundException();
        }

        $cityId = $activity->getStadt()->getId();
        foreach ($activity->getBlocks() as $block) {
            $block->setActive(null);
            $this->entityManager->persist($block);
        }

        $this->entityManager->flush();
        $this->entityManager->remove($activity);
        $this->entityManager->flush();

        return $this->redirectToRoute('city_admin_schuljahr_anzeige', [
            'id' => $cityId,
            'snack' => $translator->trans('Erfolgreich gelöscht'),
        ]);
    }

    #[Route(
        'city_admin/stadtschuljahr/kinder-loeschen',
        name: 'city_admin_schuljahr_kinder_delete',
        methods: [Request::METHOD_POST],
    )]
    public function deleteChildren(Request $request, TranslatorInterface $translator): Response
    {
        set_time_limit(600);

        $activity = $this->activeRepository->find($request->request->get('id'));
        if ($activity === null || $activity->getStadt() !== $this->getUser()?->getStadt()) {
            throw $this->createNotFoundException();
        }

        $csrfToken = $request->request->get('_token');
        if (
            !is_string($csrfToken)
            || !$this->isCsrfTokenValid('delete_school_year_children_' . $activity->getId(), $csrfToken)
        ) {
            throw $this->createAccessDeniedException('Ungültiges CSRF-Token.');
        }

        $numberOfChildren = $this->deleteSchoolYearChildrenService->deleteForSchoolYear($activity);

        return $this->redirectToRoute('city_admin_schuljahr_anzeige', [
            'id' => $activity->getStadt()->getId(),
            'snack' => $translator->trans(
                '%count% Kinder und ihre zugehörigen Daten wurden gelöscht.',
                ['%count%' => $numberOfChildren],
            ),
        ]);
    }

    /**
     * @return array{user_changed: bool, previous_roles: string[]}
     */
    private function getFormOptions(): array
    {
        $token = $this->security->getToken();
        $impersonator = $token instanceof SwitchUserToken
            ? $token->getOriginalToken()->getUser()
            : null;

        return [
            'user_changed' => $impersonator !== null,
            'previous_roles' => $impersonator?->getRoles() ?? [],
        ];
    }
}
