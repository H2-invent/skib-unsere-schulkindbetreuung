<?php

namespace App\Controller;

use App\Entity\Stadt;
use App\Entity\User;


use App\Form\Type\UserType;
use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;


class StadtadminController extends AbstractController
{
    private $manager;

    public function __construct(UserManagerInterface $manager)
    {
        $this->manager = $manager;
    }
    /**
     * @Route("/admin/stadtUser", name="admin_stadtadmin")
     */
    public function index(Request $request)
    {
        $city= $this->getDoctrine()->getRepository(Stadt::class)->find($request->get('id'));
        $user = $this->getDoctrine()->getRepository(User::class)->findBy(array('stadt'=>$city));

        return $this->render('administrator/user.html.twig', [
            'user' => $user,
            'city'=>$city
        ]);
    }
    /**
     * @Route("/admin/allUser", name="admin_showAllUser")
     */
    public function allUSer(Request $request)
    {

        $user = $this->manager->findUsers();


        return $this->render('administrator/user.html.twig', [
            'user' => $user,

        ]);
    }
    /**
     * @Route("/admin/stadtUser/neu", name="admin_stadtadmin_neu")
     */
    public function neu(Request $request,TranslatorInterface $translator,ValidatorInterface $validator)
    {
        $city = $this->getDoctrine()->getRepository(Stadt::class)->find($request->get('id'));
        $defaultData = $this->manager->createUser();;
        $errors = array();
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
                return $this->redirectToRoute('admin_stadtadmin',array('snack'=>$text,'id'=>$city->getId()));
            }catch ( \Exception $e) {
                $errorText = $translator->trans('Die E-Mail existriert Bereits. Bitte verwenden Sie eine andere Email-Adresse');
                return $this->render(
                    'administrator/error.html.twig',
                    array('error' => $errorText)
                );

            }
        }

        $title = $translator->trans('Neuen Stadtmitarbeiter anlegen');
        return $this->render('administrator/neu.html.twig',array('title'=>$title,'stadt'=>$city,'form' => $form->createView(),'errors'=>$errors));

    }
    /**
     * @Route("/admin/stadtUser/edit", name="admin_stadtadmin_edit")
     */
    public function edit(Request $request,TranslatorInterface $translator,ValidatorInterface $validator)
    {
        $city = $this->getDoctrine()->getRepository(Stadt::class)->find($request->get('id'));
        $defaultData = $this->manager->findUserBy(array('id'=>$request->get('id')));

        $errors = array();
        $form = $this->createForm(UserType::class, $defaultData);
        $form->remove('plainPassword');
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $defaultData = $form->getData();
                $userManager = $this->manager;
                $userManager->updateUser($defaultData);
                $text = $translator->trans('Erfolgreich geändert');
                return $this->redirectToRoute('admin_stadtadmin',array('snack'=>$text,'id'=>$defaultData->getStadt()->getId()));
            }catch ( \Exception $e) {
                $errorText = $translator->trans('Die E-Mail existriert Bereits. Bitte verwenden Sie eine andere Email-Adresse');
                return $this->render(
                    'administrator/error.html.twig',
                    array('error' => $errorText)
                );

            }
        }

        $title = $translator->trans('Stadtmitarbeiter bearbeiten');
        return $this->render('administrator/neu.html.twig',array('title'=>$title,'stadt'=>$city,'form' => $form->createView(),'errors'=>$errors));

    }
    /**
     * @Route("/admin/stadtUser/changePw", name="admin_stadtadmin_changePw")
     */
    public function changePw(Request $request,TranslatorInterface $translator,ValidatorInterface $validator)
    {
        $city = $this->getDoctrine()->getRepository(Stadt::class)->find($request->get('id'));
        $defaultData = $this->manager->findUserBy(array('id' => $request->get('id')));
        $errors = array();
        $form = $this->createFormBuilder($defaultData)
            ->add(
                'plainPassword',
                TextType::class,
                array('label' => 'Password*', 'required' => true, 'translation_domain' => 'form')
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
                return $this->redirectToRoute('admin_stadtadmin', array('snack'=>$text,'id' => $defaultData->getStadt()->getId()));
            } catch (\Exception $e) {
                $errorText = $translator->trans(
                    'Das Passwort konnte nicht geändert werden'
                );

                return $this->render(
                    'administrator/error.html.twig',
                    array('error' => $errorText)
                );

            }
        }
        $title = $translator->trans('Passwort ändern');
        return $this->render('administrator/neu.html.twig',array('title'=>$title,'stadt'=>$city,'form' => $form->createView(),'errors'=>$errors));

    }
    /**
     * @Route("/admin/stadtUser/toggleAdmin", name="admin_stadtadmin_toggleAdmin")
     */
    public function toggleAdmin(Request $request,TranslatorInterface $translator,ValidatorInterface $validator)
    {
        $user = $this->manager->findUserBy(array('id' => $request->get('id')));
        if($user->hasRole('ROLE_CITY_ADMIN')){
            $user->removeRole('ROLE_CITY_ADMIN');
        }else{
            $user->addRole('ROLE_CITY_ADMIN');
        }
        $this->manager->updateUser($user);
        $referer = $request
        ->headers
        ->get('referer');
        return $this->redirect($referer);
    }
    /**
     * @Route("/admin/stadtUser/deactivate", name="admin_stadtadmin_deactivate")
     */
    public function deactivateAccount(Request $request,TranslatorInterface $translator,ValidatorInterface $validator)
    {
        $user = $this->manager->findUserBy(array('id' => $request->get('id')));
        if($user->isEnabled()){
            $user->setEnabled(false);
        }else{
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
}
