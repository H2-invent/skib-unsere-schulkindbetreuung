<?php

namespace App\Controller;

use App\Entity\Organisation;
use App\Entity\Stadt;
use App\Entity\User;
use App\Form\Type\UserType;
use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class EmployeeOrganisationController extends AbstractController
{
    private $manager;

    public function __construct(UserManagerInterface $manager)
    {
        $this->manager = $manager;
    }
    /**
     * @Route("/org_edit/mitarbeiter/organisation", name="city_employee_org_show")
     */
    public function employeeOrg(Request $request)
    {
        $organisation = $this->getDoctrine()->getRepository(Organisation::class)->find($request->get('id'));

        if ($organisation->getStadt() != $this->getUser()->getStadt()) {
            throw new \Exception('Wrong City');
        }

        $user = $this->getDoctrine()->getRepository(User::class)->findBy(array('organisation' => $organisation));

        return $this->render(
            'employee_organisation/user.html.twig',
            [
                'user' => $user,
                'organisation'=>$organisation

            ]
        );
    }

    /**
     * @Route("/org_edit/mitarbeiter/organisation/neu", name="organisation_employee_new")
     */
    public function newUser(Request $request, TranslatorInterface $translator, ValidatorInterface $validator)
    {
        $organisation = $this->getDoctrine()->getRepository(Organisation::class)->find($request->get('id'));
        if ($organisation->getStadt() != $this->getUser()->getStadt()) {
            throw new \Exception('Wrong City');
        }
        $defaultData = $this->manager->createUser();
        $defaultData->setOrganisation($organisation);
        $errors = array();
        $form = $this->createForm(UserType::class, $defaultData);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $defaultData = $form->getData();
                $defaultData->setEnabled(true);
                $userManager = $this->manager;
                $userManager->updateUser($defaultData);

                return $this->redirectToRoute('city_employee_org_show', array('id' => $organisation->getId()));
            } catch (\Exception $e) {
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
}
