<?php

namespace App\Controller;

use App\Entity\Organisation;
use App\Entity\Stadt;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class CityAdminOrganisationController extends AbstractController
{
    /**
     * @Route("/city/admin/organisation/show", name="city_admin_organisation_show")
     */
    public function index(Request $request)
    {
        $city = $this->getDoctrine()->getRepository(Stadt::class)->findOneBy(array('id'=>$request->get('id'),'deleted'=>false));

        if($city != $this->getUser()->getStadt()){
            throw new \Exception('Wrong City');
        }
        $organisation = $this->getDoctrine()->getRepository(Organisation::class)->findBy(array('stadt'=>$city,'deleted'=>false));

        return $this->render('cityAdminOrganisation/organisationen.html.twig', [
            'organisation' => $organisation,
            'city'=>$city
        ]);
    }

    /**
     * @Route("/city/admin/organisation/new", name="city_admin_organisation_new",methods={"GET","POST"})
     */
    public function newSchool(Request $request, ValidatorInterface $validator,TranslatorInterface $translator)
    {
        $city = $this->getDoctrine()->getRepository(Stadt::class)->find($request->get('id'));
        if($city != $this->getUser()->getStadt()){
            throw new \Exception('Wrong City');
        }
        $organisation = new Organisation();

        $form = $this->createFormBuilder($organisation)
            ->add('name', TextType::class,['label'=>'Name der Organisation','translation_domain' => 'form'])
            ->add('adresse', TextType::class,['label'=>'Straße','translation_domain' => 'form'])
            ->add('adresszusatz', TextType::class,['label'=>'Adresszusatz','translation_domain' => 'form'])
            ->add('plz', TextType::class,['label'=>'PLZ','translation_domain' => 'form'])
            ->add('ort', TextType::class,['label'=>'Stadt','translation_domain' => 'form'])
            ->add('ansprechpartner', TextType::class,['label'=>'Ansprechpartner','translation_domain' => 'form'])
            ->add('iban', TextType::class,['label'=>'IBAN für das Lastschriftmandat','translation_domain' => 'form'])
            ->add('bic', TextType::class,['label'=>'BIC','translation_domain' => 'form'])
            ->add('bankName', TextType::class,['label'=>'Name der Bank','translation_domain' => 'form'])
            ->add('glauaubigerId', TextType::class,['label'=>'Gläubiger ID','translation_domain' => 'form'])
            ->add('infoText', TextareaType::class,['label'=>'Info Text','translation_domain' => 'form'])
            ->add('telefon', TextType::class,['label'=>'Telefonnummer','translation_domain' => 'form'])
            ->add('email', TextType::class,['label'=>'Email','translation_domain' => 'form'])
            ->add('smptServer', TextType::class,['label'=>'SMTP Server','translation_domain' => 'form'])
            ->add('smtpPort', TextType::class,['label'=>'SMTP Port','translation_domain' => 'form'])
            ->add('smtpUser', TextType::class,['label'=>'SMTP Username','translation_domain' => 'form'])
            ->add('smtpPassword', TextType::class,['label'=>'SMTP Passwort','translation_domain' => 'form'])
            ->add('submit', SubmitType::class, ['label' => 'Speichern','translation_domain' => 'form'])
            ->getForm();
        $form->handleRequest($request);
        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $organisation = $form->getData();
            $organisation->setStadt($city);
            $errors = $validator->validate($organisation);
            if(count($errors)== 0) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($organisation);
                $em->flush();
                return $this->redirectToRoute('city_admin_organisation_show',array('id'=>$city->getId()));
            }

        }
        $title = $translator->trans('Organisation anlegen');
        return $this->render('administrator/neu.html.twig',array('title'=>$title,'stadt'=>$city,'form' => $form->createView(),'errors'=>$errors));

    }
}
