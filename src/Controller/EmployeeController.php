<?php

namespace App\Controller;

use App\Entity\Stadt;
use App\Entity\User;
use App\Form\Type\UserType;
use App\Security\UserManagerInterface;
use App\Service\InvitationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class EmployeeController extends AbstractController
{
    private $manager;
    private $availRole;

    public function __construct(UserManagerInterface $manager)
    {
        $this->manager = $manager;
        $this->availRole = array(
            'ROLE_CITY_DASHBOARD' => 'ROLE_CITY_DASHBOARD',
            'ROLE_CITY_SCHOOL' => 'ROLE_CITY_SCHOOL',
            'ROLE_CITY_REPORT' => 'ROLE_CITY_REPORT',
            'ROLE_CITY_NEWS' => 'ROLE_CITY_NEWS',
        );
    }

    /**
     * @Route("/city_admin/mitarbeiter/stadt", name="city_employee_show")
     */
    public function index(Request $request)
    {
        $city = $this->getDoctrine()->getRepository(Stadt::class)->find($request->get('id'));
        if ($city != $this->getUser()->getStadt()) {
            throw new \Exception('Wrong City');
        }
        $user = $this->getDoctrine()->getRepository(User::class)->findBy(array('stadt' => $city, 'organisation' => null));
        return $this->render(
            'employee/user.html.twig',
            [
                'user' => $user,
                'city' => $city
            ]
        );
    }

    /**
     * @Route("/city_admin/mitarbeiter/stadt/neu", name="city_employee_new")
     */
    public function newUser(Request $request, TranslatorInterface $translator, ValidatorInterface $validator,InvitationService $invitationService)
    {
        $city = $this->getDoctrine()->getRepository(Stadt::class)->find($request->get('id'));
        if ($city != $this->getUser()->getStadt()) {
            throw new \Exception('Wrong City');
        }
        $defaultData = $this->manager->createUser();
        $defaultData->setStadt($city);
        $errors = array();
        $form = $this->createForm(UserType::class, $defaultData);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $defaultData = $form->getData();
                $defaultData->setEnabled(true);
                $userManager = $this->manager;
                $userManager->updateUser($defaultData);
                $text = $translator->trans('Erfolgreich angelegt');
                $invitationService->inviteNewUser($defaultData,$this->getUser());
                return $this->redirectToRoute('city_employee_show', array('snack' => $text, 'id' => $city->getId()));
            } catch (\Exception $e) {
                $userManager = $this->manager;
                $errorText = $translator->trans(
                    'Unbekannter Fehler'
                );
                if ($userManager->findUserByEmail($defaultData->getEmail())) {
                    $errorText = $translator->trans(
                        'Die E-Mail existriert Bereits. Bitte verwenden Sie eine andere Email-Adresse'
                    );
                } elseif ($userManager->findUserByUsername($defaultData->getUsername())) {
                    $errorText = $translator->trans(
                        'Der Benutername existriert Bereits. Bitte verwenden Sie eine anderen Benutzername'
                    );
                }

                return $this->render(
                    'administrator/error.html.twig',
                    array('error' => $errorText)
                );

            }
        }
        $title = $translator->trans('Neuen Stadtmitarbeiter anlegen');
        return $this->render(
            'administrator/neu.html.twig',
            array('title' => $title, 'stadt' => $city, 'form' => $form->createView(), 'errors' => $errors)
        );

    }

    /**
     * @Route("/city_admin/mitarbeiter/stadt/edit", name="city_employee_edit")
     */
    public function edit(Request $request, TranslatorInterface $translator, ValidatorInterface $validator)
    {

        $defaultData = $this->manager->findUserBy(array('id' => $request->get('id')));
        if ($defaultData->getStadt() != $this->getUser()->getStadt()) {
            throw new \Exception('Wrong City');
        }
        $city = $defaultData->getStadt();
        $errors = array();
        $form = $this->createForm(UserType::class, $defaultData);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $defaultData = $form->getData();
                $defaultData->setEnabled(true);
                $userManager = $this->manager;
                $userManager->updateUser($defaultData);
                $text = $translator->trans('Erfolgreich gespeichert');
                return $this->redirectToRoute('city_employee_show', array('snack' => $text, 'id' => $defaultData->getStadt()->getId()));
            } catch (\Exception $e) {
                $errorText = $translator->trans(
                    'Die E-Mail existriert Bereits. Bitte verwenden Sie eine andere Email-Adresse'
                );

                return $this->render(
                    'administrator/error.html.twig',
                    array('error' => $errorText)
                );

            }
        }

        $title = $translator->trans('Neuen Stadtmitarbeiter anlegen');

        return $this->render(
            'administrator/neu.html.twig',
            array('title' => $title, 'stadt' => $city, 'form' => $form->createView(), 'errors' => $errors)
        );

    }

    /**
     * @Route("/city_admin/stadtUser/deactivate", name="city_admin_city_employee_deactivate")
     */
    public function deactivateAccount(Request $request, TranslatorInterface $translator, ValidatorInterface $validator)
    {
        $user = $this->manager->findUserBy(array('id' => $request->get('id')));
        if ($user->getStadt() != $this->getUser()->getStadt()) {
            throw new \Exception('Wrong City');
        }

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

    /**
     * @Route("/city_admin/mitarbeiter/changePw", name="city_admin_mitarbeiter_changePw")
     * * @Route("/org_admin/mitarbeiter/changePw", name="org_admin_mitarbeiter_changePw")
     */
    public function changePw(Request $request, TranslatorInterface $translator, ValidatorInterface $validator)
    {
        $user = $this->manager->findUserBy(array('id' => $request->get('id')));
        if ($user->getStadt() != $this->getUser()->getStadt()) {
            throw new \Exception('Wrong City');
        }
        $city = $user->getStadt();
        $errors = array();
        $form = $this->createFormBuilder($user)
            ->add(
                'plainPassword',
                TextType::class,
                array('label' => 'Passwort', 'required' => true, 'translation_domain' => 'form')
            )
            ->add('save', SubmitType::class, ['label' => 'Save'])
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $defaultData = $form->getData();
                $userManager = $this->manager;
                $userManager->updateUser($defaultData);

                if ($user->getStadt() !== null) {
                    $text = $translator->trans('Erfolgreich gespeichert');
                    return $this->redirectToRoute(
                        'city_employee_show',
                        array('snack' => $text, 'id' => $defaultData->getStadt()->getId())
                    );

                }
            } catch (\Exception $e) {
                $errorText = $translator->trans(
                    'Das Passwort konnte nich geändert werden'
                );

                return $this->render(
                    'administrator/error.html.twig',
                    array('error' => $errorText)
                );

            }
        }
        $title = $translator->trans('Passwort ändern');

        return $this->render(
            'administrator/neu.html.twig',
            array('title' => $title, 'stadt' => $city, 'form' => $form->createView(), 'errors' => $errors)
        );

    }

    /**
     * @Route("/city_admin/mitarbeiter/delete", name="city_admin_mitarbeiter_delete")
     */
    public function delete(Request $request, TranslatorInterface $translator, ValidatorInterface $validator)
    {
        $user = $this->manager->findUserBy(array('id' => $request->get('id')));
        if ($user->getStadt() != $this->getUser()->getStadt()) {
            throw new \Exception('Wrong City');
        }
        $this->manager->deleteUser($user);
        if ($user->getStadt() !== null) {
            $text = $translator->trans('Erfolgreich gelöscht');
            return $this->redirectToRoute(
                'city_employee_show',
                array('snack' => $text, 'id' => $user->getStadt()->getId())
            );

        }

    }

    /**
     * @Route("login/city_admin/userRoles", name="city_admin_mitarbeiter_roles")
     */
    public function showUserRolesAction(Request $request, TranslatorInterface $translator)
    {
        $user = $this->manager->findUserBy(array('id' => $request->get('id')));
        if ($user->getStadt() != $this->getUser()->getStadt()) {
            throw new \Exception('Wrong City');
        }

        $roles = array();
        foreach ($user->getRoles() as $data) {
            $roles[$data] = true;
        }

        $form = $this->createFormBuilder($roles);
        foreach ($this->availRole as $key => $data) {
            $form->add(
                $key,
                CheckboxType::class,
                array('required' => false, 'label' => $data, 'translation_domain' => 'form')
            );
        }
        $form->add('Speichern', SubmitType::class, array('translation_domain' => 'form'));
        $formI = $form->getForm();
        $formI->handleRequest($request);


        if ($formI->isSubmitted() && $formI->isValid()) {
            $user = $this->addNewRoles($user, $this->availRole, $formI->getData());
            $this->manager->updateUser($user);

            $text = $translator->trans('Berechtigungen erfolgreich gesetzt');
            return $this->redirectToRoute('city_employee_show', array('snack' => $text, 'id' => $user->getStadt()->getId()));
        }

        return $this->render('administrator/EditRoles.twig', array('user' => $user, 'form' => $formI->createView()));
    }

    private function addNewRoles(User $user, $availRole, $roles)
    {
        foreach ($availRole as $data) {
            $user->removeRole($data);
        }

        foreach ($roles as $key => $item) {
            if ($item === true) {
                $user->addRole($key);
            }
        }
        return $user;
    }
}
