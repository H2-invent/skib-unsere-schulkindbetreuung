<?php

namespace App\Controller;

use App\Entity\Stadt;
use App\Entity\User;
use App\Form\Type\UserType;
use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class EmployeeController extends AbstractController
{
    private $manager;

    public function __construct(UserManagerInterface $manager)
    {
        $this->manager = $manager;
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
        $user = $this->getDoctrine()->getRepository(User::class)->findBy(array('stadt' => $city));

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
    public function newUser(Request $request, TranslatorInterface $translator, ValidatorInterface $validator)
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
                $userManager = $this->manager;
                $userManager->updateUser($defaultData);

                return $this->redirectToRoute('city_employee_show', array('id' => $city->getId()));
            } catch (\Exception $e) {
                $userManager = $this->manager;
                $errorText = $translator->trans(
                    'Unbekannter Fehler'
                );
                if($userManager->findUserByEmail($defaultData->getEmail())){
                    $errorText = $translator->trans(
                        'Die Email existriert Bereits. Bitte verwenden Sie eine andere Email-Adresse'
                    );
                }elseif($userManager->findUserByUsername($defaultData->getUsername())) {
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
        $form->remove('plainPassword');
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $defaultData = $form->getData();
                $defaultData->setEnabled(true);
                $userManager = $this->manager;
                $userManager->updateUser($defaultData);

                return $this->redirectToRoute('city_employee_show', array('id' => $defaultData->getStadt()->getId()));
            } catch (\Exception $e) {
                $errorText = $translator->trans(
                    'Die Email existriert Bereits. Bitte verwenden Sie eine andere Email-Adresse'
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
        $previous = $request->getSession()->get('previous');
        $url = "";
        if ($previous) {
            $url = $previous;
        }
        $referer = $request
            ->headers
            ->get('referer');

        return $this->redirect($referer);

    }

    /**
     * @Route("/city_admin/mitarbeiter/changePw", name="city_admin_mitarbeiter_changePw")
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

                if ($user->getStadt() != null) {
                    return $this->redirectToRoute(
                        'city_employee_show',
                        array('id' => $defaultData->getStadt()->getId())
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
        if ($user->getStadt() != null) {
            return $this->redirectToRoute(
                'city_employee_show',
                array('id' => $user->getStadt()->getId())
            );

        }

    }
    /**
     * @Route("login/companyAdmin/userRoles", name="company_showUserRoles")
     */
    public function showUserRolesAction(Request $request)
    {
        $user = $this->getDoctrine()->getRepository('AppBundle:LoginUser')->find($request->get('id'));
        $bauherr = $this->getDoctrine()->getRepository('AppBundle:bauherren')->find($user->getUserId());
        $company = $this->getDoctrine()->getRepository('AppBundle:Company')->find($this->getUser()->getCompanyId());
        if ($user->getCompanyId() != $company->getId()) {
            throw new \Exception('fehlerhafte Eingabe');
        }
        $roles = array();
        foreach ($user->getRoles() as $data) {
            $roles[$data] = true;
        }

        $availRole = array(
            'ROLE_COMPANY_MASTER' => 'Darf alles tun (Administrator',
            'ROLE_COSTUMER' => 'Darf Kunden sehen und bearbeiten',
            'ROLE_FINANCE' => 'Darf Finanzen sehen und bearbeiten',
            'ROLE_RECHNUNG' => 'Darf Rechnungen sehen und bearbeiten',
            'ROLE_WARTUNG' => 'Darf Daueraufträge sehen und bearbeiten',
            'ROLE_ISSUE' => 'Darf Probleme sehen und bearbeiten',
            'ROLE_PROJECT' => 'Darf Projekte sehen und bearbeiten',
            'ROLE_POSTEN' => 'Darf Projektdetails sehen und bearbeiten',
            'ROLE_MITARBEITER' => 'Darf Mitarbeiter sehen und bearbeiten',
            'ROLE_ANGEBOT' => 'Darf Angebote sehen und bearbeiten',
            'ROLE_GROUPS' => 'Darf Gruppen sehen und bearbeiten',
            'ROLE_MATERIAL' => 'Material sehen und bearbeiten',
            'ROLE_ORGANIGRAMM' => 'Darf das Organigramm bearbeiten',
            'ROLE_SHARE' => 'Darf Projekte teilen und geteilte Projekte Annehmen',
            'ROLE_STUNDEN' => 'Darf Stunden sehen und bearbeiten',
            'ROLE_SURVEY' => 'Darf Fragebögen sehen und bearbeiten',
            'ROLE_URLAUB' => 'Darf Urlaube sehen und genehmigen',
        );

        $form = $this->createFormBuilder($roles);
        foreach ($availRole as $key => $data) {
            $form->add(
                $key,
                CheckboxType::class,
                array('required' => false, 'label' => $data)
            );
        }
        $form->add('Speichern', SubmitType::class);
        $formI = $form->getForm();
        $formI->handleRequest($request);


        if ($formI->isSubmitted() && $formI->isValid()) {

            $roles = $formI->getData();

            foreach ($user->getRoles() as $item) {
                $user->removeRole($item);
            }
            $user->addRole('ROLE_USER');
            $user->addRole('ROLE_COMPANY');

            foreach ($roles as $key => $item) {
                if ($item == true) {
                    $user->addRole($key);
                }
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('company_showUserRoles', array('id' => $user->getId()));
        }

        return $this->render('lucky/companyEditRoles.twig', array('user' => $bauherr, 'form' => $formI->createView()));
    }
}
