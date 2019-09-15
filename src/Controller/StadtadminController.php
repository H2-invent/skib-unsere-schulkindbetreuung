<?php

namespace App\Controller;

use App\Entity\Stadt;
use App\Entity\User;


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
     * @Route("/admin/stadtUser/neu", name="admin_stadtadmin_neu")
     */
    public function neu(Request $request,TranslatorInterface $translator,ValidatorInterface $validator)
    {
        $city = $this->getDoctrine()->getRepository(Stadt::class)->find($request->get('id'));
        $defaultData = [];
        $errors = array();
        $form = $this->createFormBuilder($defaultData)
            ->add('username', TextType::class,array('label'=>'Username*','required'=>true,'translation_domain' => 'form'))
            ->add('email', EmailType::class,array('label'=>'Email*','required'=>true,'translation_domain' => 'form'))
            ->add('password', TextType::class,array('required'=>false,'label'=>'Password*','translation_domain' => 'form'))
            ->add('vorname', EmailType::class,array('label'=>'Vorname','required'=>true,'translation_domain' => 'form'))
            ->add('nachname', EmailType::class,array('label'=>'Name','required'=>true,'translation_domain' => 'form'))
            ->add('birthday', BirthdayType::class,array('label'=>'Geburtstag','required'=>true,'translation_domain' => 'form'))
            ->add('save', SubmitType::class, ['label' => 'Save'])
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $defaultData = $form->getData();
                $userManager = $this->manager;
                $user = $userManager->createUser();
                $user->setPlainPassword($defaultData['password']);
                $user->setEmail($defaultData['email']);
                $user->setUsername($defaultData['username']);
                $user->setVorname($defaultData['vorname']);
                $user->setNachname($defaultData['nachname']);
                $user->setBirthday($defaultData['birthday']);
                $user->setStadt($city);
                $user->setEnabled(true);
                $userManager->updateUser($user);

                return $this->redirectToRoute('admin_stadtadmin',array('id'=>$city->getId()));
            }catch ( \Exception $e) {
                $errorText = $translator->trans('Die Email existriert Bereits. Bitte verwenden Sie eine andere Email-Adresse');
                return $this->render(
                    'administrator/error.html.twig',
                    array('error' => $errorText)
                );

            }
        }

        $title = $translator->trans('Neuen Stadtmitarbeiter anlegen');
        return $this->render('administrator/neu.html.twig',array('title'=>$title,'stadt'=>$city,'form' => $form->createView(),'errors'=>$errors));

    }
}
