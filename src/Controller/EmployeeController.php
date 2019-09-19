<?php

namespace App\Controller;

use App\Entity\Stadt;
use App\Entity\User;
use App\Form\Type\UserType;
use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
     * @Route("/city/admin/mitarbeiter/stadt", name="city_employee_show")
     */
    public function index(Request $request)
    {
        $city= $this->getDoctrine()->getRepository(Stadt::class)->find($request->get('id'));
        if($city != $this->getUser()->getStadt()){
            throw new \Exception('Wrong City');
        }
        $user = $this->getDoctrine()->getRepository(User::class)->findBy(array('stadt'=>$city));
        return $this->render('employee/user.html.twig', [
            'user' => $user,
            'city'=>$city
        ]);
    }
    /**
     * @Route("/city/admin/mitarbeiter/stadt/neu", name="city_employee_new")
     */
    public function newUser(Request $request,TranslatorInterface $translator,ValidatorInterface $validator)
    {
        $city = $this->getDoctrine()->getRepository(Stadt::class)->find($request->get('id'));
        if($city != $this->getUser()->getStadt()){
            throw new \Exception('Wrong City');
        }
        $defaultData = $this->manager-> createUser();
        $defaultData->setStadt($city);
        $errors = array();
        $form = $this->createForm(UserType::class, $defaultData);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $defaultData = $form->getData();
                $userManager = $this->manager;
                $userManager->updateUser($defaultData);
                return $this->redirectToRoute('city_employee_show',array('id'=>$city->getId()));
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
    /**
     * @Route("/city/admin/mitarbeiter/stadt/edit", name="city_employee_edit")
     */
    public function edit(Request $request,TranslatorInterface $translator,ValidatorInterface $validator)
    {

        $defaultData = $this->manager->findUserBy(array('id'=>$request->get('id')));
        if($defaultData->getStadt() != $this->getUser()->getStadt()){
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
                return $this->redirectToRoute('city_employee_show',array('id'=>$defaultData->getStadt()->getId()));
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
    /**
     * @Route("/city/admin/stadtUser/deactivate", name="city_admin_city_employee_deactivate")
     */
    public function deactivateAccount(Request $request,TranslatorInterface $translator,ValidatorInterface $validator)
    {
        $user = $this->manager->findUserBy(array('id'=>$request->get('id')));
        if($user->getStadt() != $this->getUser()->getStadt()){
            throw new \Exception('Wrong City');
        }

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
