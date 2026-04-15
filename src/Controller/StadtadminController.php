<?php

namespace App\Controller;

use App\Entity\Stadt;
use App\Entity\User;
use App\Form\Type\UserType;
use App\Security\UserManagerInterface;
use App\Service\InvitationService;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class StadtadminController extends AbstractController
{
    public function __construct(
        private UserManagerInterface $manager,
        private ManagerRegistry $managerRegistry,
    ) {
    }

    #[Route(path: '/admin/stadtUser', name: 'admin_stadtadmin')]
    public function index(Request $request)
    {
        $city = $this->managerRegistry->getRepository(Stadt::class)->find($request->get('id'));
        $user = $this->managerRegistry->getRepository(User::class)->findBy(['stadt' => $city]);

        return $this->render('administrator/user.html.twig', [
            'user' => $user,
            'city' => $city,
        ]);
    }

    #[Route(path: '/admin/allUser', name: 'admin_showAllUser')]
    public function allUSer(Request $request)
    {
        $user = $this->manager->findUsers();

        return $this->render('administrator/user.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route(path: '/admin/stadtUser/neu', name: 'admin_stadtadmin_neu')]
    public function neu(Request $request, TranslatorInterface $translator, ValidatorInterface $validator, InvitationService $invitationService)
    {
        $city = $this->managerRegistry->getRepository(Stadt::class)->find($request->get('id'));
        $defaultData = $this->manager->createUser();
        $errors = [];
        $form = $this->createForm(UserType::class, $defaultData);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $defaultData = $form->getData();
                $defaultData->setStadt($city);
                $defaultData->setEnabled(true);
                $defaultData->addRole('ROLE_CTY_ADMIN');
                $this->manager->updateUser($defaultData);
                $text = $translator->trans('Erfolgreich angelegt');
                $invitationService->inviteNewUser($defaultData, $this->getUser());

                return $this->redirectToRoute('admin_stadtadmin', ['snack' => $text, 'id' => $city->getId()]);
            } catch (\Exception) {
                $errorText = $translator->trans('Die E-Mail existriert Bereits. Bitte verwenden Sie eine andere Email-Adresse');

                return $this->render(
                    'administrator/error.html.twig',
                    ['error' => $errorText]
                );
            }
        }

        $title = $translator->trans('Neuen Stadtmitarbeiter anlegen');

        return $this->render('administrator/neu.html.twig', ['title' => $title, 'stadt' => $city, 'form' => $form, 'errors' => $errors]);
    }

    #[Route(path: '/admin/stadtUser/edit', name: 'admin_stadtadmin_edit')]
    public function edit(Request $request, TranslatorInterface $translator, ValidatorInterface $validator)
    {
        $city = $this->managerRegistry->getRepository(Stadt::class)->find($request->get('id'));
        $defaultData = $this->manager->findUserBy(['id' => $request->get('id')]);

        $errors = [];
        $form = $this->createForm(UserType::class, $defaultData);
        $form->remove('plainPassword');
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $defaultData = $form->getData();
                $userManager = $this->manager;
                $userManager->updateUser($defaultData);
                $text = $translator->trans('Erfolgreich geändert');

                return $this->redirectToRoute('admin_stadtadmin', ['snack' => $text, 'id' => $defaultData->getStadt()->getId()]);
            } catch (\Exception) {
                $errorText = $translator->trans('Die E-Mail existriert Bereits. Bitte verwenden Sie eine andere Email-Adresse');

                return $this->render(
                    'administrator/error.html.twig',
                    ['error' => $errorText]
                );
            }
        }

        $title = $translator->trans('Stadtmitarbeiter bearbeiten');

        return $this->render('administrator/neu.html.twig', ['title' => $title, 'stadt' => $city, 'form' => $form, 'errors' => $errors]);
    }

    #[Route(path: '/admin/stadtUser/changePw', name: 'admin_stadtadmin_changePw')]
    public function changePw(Request $request, TranslatorInterface $translator, ValidatorInterface $validator)
    {
        $city = $this->managerRegistry->getRepository(Stadt::class)->find($request->get('id'));
        $defaultData = $this->manager->findUserBy(['id' => $request->get('id')]);
        $errors = [];
        $form = $this->createFormBuilder($defaultData)
            ->add(
                'plainPassword',
                TextType::class,
                ['label' => 'Password*', 'required' => true, 'translation_domain' => 'form']
            )
            ->add('save', SubmitType::class, ['label' => 'Save'])
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $defaultData = $form->getData();
                $userManager = $this->manager;
                $userManager->updateUser($defaultData);
                $text = $translator->trans('Passwort erfolgreich geändert');

                return $this->redirectToRoute('admin_stadtadmin', ['snack' => $text, 'id' => $defaultData->getStadt()->getId()]);
            } catch (\Exception) {
                $errorText = $translator->trans(
                    'Das Passwort konnte nicht geändert werden'
                );

                return $this->render(
                    'administrator/error.html.twig',
                    ['error' => $errorText]
                );
            }
        }
        $title = $translator->trans('Passwort ändern');

        return $this->render('administrator/neu.html.twig', ['title' => $title, 'stadt' => $city, 'form' => $form, 'errors' => $errors]);
    }

    #[Route(path: '/admin/stadtUser/toggleAdmin', name: 'admin_stadtadmin_toggleAdmin')]
    public function toggleAdmin(Request $request, TranslatorInterface $translator, ValidatorInterface $validator)
    {
        $user = $this->manager->findUserBy(['id' => $request->get('id')]);
        if ($user->hasRole('ROLE_CITY_ADMIN')) {
            $user->removeRole('ROLE_CITY_ADMIN');
        } else {
            $user->addRole('ROLE_CITY_ADMIN');
        }
        $this->manager->updateUser($user);
        $referer = $request
        ->headers
        ->get('referer');

        return $this->redirect($referer);
    }

    #[Route(path: '/admin/stadtUser/toggleSuperAdmin', name: 'admin_stadtadmin_toggleSuperAdmin')]
    public function toggleSuperAdmin(Request $request, TranslatorInterface $translator, ValidatorInterface $validator)
    {
        $user = $this->manager->findUserBy(['id' => $request->get('id')]);
        if ($user->hasRole('ROLE_ADMIN')) {
            $user->removeRole('ROLE_ADMIN');
        } else {
            $user->addRole('ROLE_ADMIN');
        }
        $this->manager->updateUser($user);
        $referer = $request
            ->headers
            ->get('referer');

        return $this->redirect($referer);
    }

    #[Route(path: '/admin/stadtUser/deactivate', name: 'admin_stadtadmin_deactivate')]
    public function deactivateAccount(Request $request, TranslatorInterface $translator, ValidatorInterface $validator)
    {
        $user = $this->manager->findUserBy(['id' => $request->get('id')]);
        if ($user->isEnabled()) {
            $user->setEnabled(false);
        } else {
            $user->setEnabled(true);
        }
        $this->manager->updateUser($user);

        $referer = $request
            ->headers
            ->get('referer');

        return $this->redirect($referer);
    }
}
