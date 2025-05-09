<?php

namespace App\Controller;

use App\Entity\Organisation;
use App\Entity\User;
use App\Form\Type\UserType;

use App\Security\UserManagerInterface;
use App\Service\InvitationService;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class EmployeeOrganisationController extends AbstractController
{
    private $manager;
    private $availRole;
    private LoggerInterface $logger;
    public function __construct(UserManagerInterface $manager, LoggerInterface $logger, private ManagerRegistry $managerRegistry)
    {
        $this->logger = $logger;
        $this->manager = $manager;
        $this->availRole = array(
            'ROLE_ORG_REPORT' => 'ROLE_ORG_REPORT',
            'ROLE_ORG_CHILD_CHANGE' => 'ROLE_ORG_CHILD_CHANGE',
            'ROLE_ORG_CHILD_EMAIL_CHANGE' => 'ROLE_ORG_CHILD_EMAIL_CHANGE',
            'ROLE_ORG_CHILD_SCHOOLYEAR_CHANGE' => 'ROLE_ORG_CHILD_SCHOOLYEAR_CHANGE',
            'ROLE_ORG_CHILD_SHOW' => 'ROLE_ORG_CHILD_SHOW',
            'ROLE_ORG_ACCOUNTING'=>'ROLE_ORG_ACCOUNTING',
            'ROLE_ORG_BLOCK_MANAGEMENT'=>'ROLE_ORG_BLOCK_MANAGEMENT',
            'ROLE_ORG_BLOCK_DELETE'=>'ROLE_ORG_BLOCK_DELETE',
            'ROLE_ORG_SHOOL'=>'ROLE_ORG_SHOOL',
            'ROLE_ORG_NEWS'=>'ROLE_ORG_NEWS',
            'ROLE_ORG_CHILD_DELETE'=>'ROLE_ORG_CHILD_DELETE',
            'ROLE_ORG_ACCEPT_CHILD'=>'ROLE_ORG_ACCEPT_CHILD',
            'ROLE_ORG_BLOCK_DEACTIVATE'=>'ROLE_ORG_BLOCK_DEACTIVATE',
            'ROLE_ORG_SEE_PRICE'=>'ROLE_ORG_SEE_PRICE',
            'ROLE_ORG_VIEW_NOTICE'=>'ROLE_ORG_VIEW_NOTICE',
            'ROLE_ORG_EDIT_NOTICE'=>'ROLE_ORG_EDIT_NOTICE',


            'ROLE_ORG_FERIEN_EDITOR'=>'ROLE_ORG_FERIEN_EDITOR',
            'ROLE_ORG_FERIEN_REPORT'=>'ROLE_ORG_FERIEN_REPORT',
            'ROLE_ORG_FERIEN_ORDERS'=>'ROLE_ORG_FERIEN_ORDERS',
            'ROLE_ORG_FERIEN_CHECKIN'=>'ROLE_ORG_FERIEN_CHECKIN',
            'ROLE_ORG_FERIEN_ADMIN'=>'ROLE_ORG_FERIEN_ADMIN',
            'ROLE_ORG_FERIEN_STORNO'=>'ROLE_ORG_FERIEN_STORNO',
            'ROLE_ORG_CHECKIN_SHOW'=>'ROLE_ORG_CHECKIN_SHOW',
            'ROLE_ORG_KVJS'=>'ROLE_ORG_KVJS'
        );
    }
    /**
     * @Route("/org_edit/mitarbeiter/organisation", name="city_employee_org_show")
     */
    public function employeeOrg(Request $request)
    {
        $organisation = $this->managerRegistry->getRepository(Organisation::class)->find($request->get('id'));

        if ($organisation->getStadt() != $this->getUser()->getStadt()) {
            throw new \Exception('Wrong City');
        }

        $user = $this->managerRegistry->getRepository(User::class)->findBy(array('organisation' => $organisation));

        return $this->render(
            'employee_organisation/user.html.twig',
            [
                'user' => $user,
                'organisation'=>$organisation

            ]
        );
    }
    /**
     * @Route("/org_admin/mitarbeiter/edit", name="org_employee_edit",methods={"POST","GET"})
     */
    public function edit(Request $request, TranslatorInterface $translator, ValidatorInterface $validator)
    {

        $defaultData = $this->manager->findUserBy(array('id' => $request->get('id')));
        if ($defaultData->getOrganisation() != $this->getUser()->getOrganisation()) {
            throw new \Exception('Wrong City');
        }
        $city = $defaultData->getStadt();
        $errors = array();
        $form = $this->createForm(UserType::class, $defaultData,array('schulen'=>$this->getUser()->getOrganisation()->getSchule()));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $defaultData = $form->getData();
                $userManager = $this->manager;
                $userManager->updateUser($defaultData);
                $text = $translator->trans('Erfolgreich gespeichert');
                return $this->redirectToRoute('city_employee_org_show', array('snack'=>$text,'id' => $defaultData->getOrganisation()->getId()));
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
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
     * @Route("/org_edit/mitarbeiter/organisation/neu", name="organisation_employee_new")
     */
    public function newUser(Request $request, TranslatorInterface $translator, ValidatorInterface $validator,InvitationService $invitationService)
    {
        $organisation = $this->managerRegistry->getRepository(Organisation::class)->find($request->get('id'));
        if ($organisation->getStadt() != $this->getUser()->getStadt()) {
            throw new \Exception('Wrong City');
        }
        $defaultData = $this->manager->createUser();
        $defaultData->setOrganisation($organisation);
        $defaultData->setStadt($organisation->getStadt());
        $errors = array();
        $form = $this->createForm(UserType::class, $defaultData,array('schulen'=>$organisation->getSchule()));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $defaultData = $form->getData();
                $defaultData->setEnabled(true);
                $userManager = $this->manager;
                $userManager->updateUser($defaultData);
                $invitationService->inviteNewUser($defaultData,$this->getUser());
                $text = $translator->trans('Erfolgreich gespeichert');
                return $this->redirectToRoute('city_employee_org_show', array('snack'=>$text,'id' => $organisation->getId()));
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
                $userManager = $this->manager;
                $errorText = $translator->trans(
                    'Unbekannter Fehler'
                );
                if ($userManager->findUserByEmail($defaultData->getEmail())) {
                    $errorText = $translator->trans(
                        'Die Email existriert Bereits. Bitte verwenden Sie eine andere Email-Adresse'
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
        $title = $translator->trans('Neuen Organisationsmitarbeiter anlegen');

        return $this->render(
            'administrator/neu.html.twig',
            array('title' => $title, 'form' => $form->createView(), 'errors' => $errors)
        );
    }

    /**
     * @Route("/org_edit/mitarbeiter/organisation/activate", name="organisation_employee_activate")
     */
    public function activate(Request $request, TranslatorInterface $translator, ValidatorInterface $validator)
    {
       $user = $this->manager->findUserBy(array('id'=>$request->get('id')));
        $organisation = $user->getOrganisation();
        if ($organisation->getStadt() != $this->getUser()->getStadt()) {
            throw new \Exception('Wrong City');
        }
       if($user->isEnabled()){
            $user->setEnabled(false);
        }else{
           $user->setEnabled(true);
        }
        $this->manager->updateUser($user);
                $referer = $request
                    ->headers
                    ->get('referer');
        return $this->redirect($referer);
    }
    /**
     * @Route("/org_edit/mitarbeiter/organisation/delete", name="organisation_employee_delete")
     */
    public function delete(Request $request, TranslatorInterface $translator, ValidatorInterface $validator)
    {
        $user = $this->manager->findUserBy(array('id'=>$request->get('id')));
        $organisation = $user->getOrganisation();
        if ($organisation->getStadt() != $this->getUser()->getStadt()) {
            throw new \Exception('Wrong City');
        }

        $this->manager->deleteUser($user);
        $referer = $request
            ->headers
            ->get('referer');
        return $this->redirect($referer);
    }
    /**
     * @Route("/city_edit/mitarbeiter/organisation/toggleAdmin", name="organisation_employee_setAdmin")
     */
    public function makeAdmin(Request $request, TranslatorInterface $translator, ValidatorInterface $validator)
    {
        $user = $this->manager->findUserBy(array('id'=>$request->get('id')));
        $organisation = $user->getOrganisation();
        if ($organisation->getStadt() != $this->getUser()->getStadt()) {
            throw new \Exception('Wrong City');
        }

        $user = $this->manager->findUserBy(array('id' => $request->get('id')));
        if($user->hasRole('ROLE_ORG_ADMIN')){
            $user->removeRole('ROLE_ORG_ADMIN');
        }else{
            $user->addRole('ROLE_ORG_ADMIN');
        }
        $this->manager->updateUser($user);
        $referer = $request
            ->headers
            ->get('referer');
        return $this->redirect($referer);
    }
    /**
     * @Route("login/org_edit/userRoles", name="org_admin_mitarbeiter_roles")
     */
    public function userRoles(Request $request, TranslatorInterface $translator)
    {
        $user = $this->manager->findUserBy(array('id'=>$request->get('id')));

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
                array('required' => false, 'label' => $data,'translation_domain' => 'form')
            );
        }
        $form->add('Speichern', SubmitType::class,array('translation_domain' => 'form'));
        $formI = $form->getForm();
        $formI->handleRequest($request);


        if ($formI->isSubmitted() && $formI->isValid()) {

            $roles = $formI->getData();

            foreach ($this->availRole as $item) {
                $user->removeRole($item);
            }
            $user->addRole('ROLE_USER');

            foreach ($roles as $key => $item) {
                if ($item === true) {
                    $user->addRole($key);
                }
            }
            $this->manager->updateUser($user);

            $text = $translator->trans('Erfolgreich gespeichert');
            return $this->redirectToRoute('city_employee_org_show', array('snack'=>$text,'id' => $user->getOrganisation()->getId()));
        }

        return $this->render('administrator/EditRoles.twig', array('user'=>$user, 'form' => $formI->createView()));
    }
}
