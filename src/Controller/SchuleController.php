<?php

namespace App\Controller;

use App\Entity\Organisation;
use App\Entity\Schule;
use App\Entity\Stadt;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Vich\UploaderBundle\Form\Type\VichFileType;
use Vich\UploaderBundle\Form\Type\VichImageType;

class SchuleController extends AbstractController
{
    /**
     * @Route("/city_schule/show", name="city_admin_schule_show",methods={"GET"})
     */
    public function index(Request $request)
    {
        $city = $this->getDoctrine()->getRepository(Stadt::class)->findOneBy(array('id'=>$request->get('id'),'deleted'=>false));
        if($city != $this->getUser()->getStadt()){
            throw new \Exception('Wrong City');
        }
        $schools = $this->getDoctrine()->getRepository(Schule::class)->findBy(array('stadt'=>$city,'deleted'=>false));

        return $this->render('cityAdminSchule/schulen.html.twig', [
            'schulen' => $schools,
            'city'=>$this->getUser()->getStadt()
        ]);
    }
    /**
     * @Route("/city_schule/new", name="city_admin_schule_new",methods={"GET","POST"})
     */
    public function newSchool(Request $request, ValidatorInterface $validator,TranslatorInterface $translator)
    {
        $city = $this->getDoctrine()->getRepository(Stadt::class)->find($request->get('id'));
        $organisations = $this->getDoctrine()->getRepository(Organisation::class)->findBy(array('stadt'=>$city));
        if($city != $this->getUser()->getStadt()){
            throw new \Exception('Wrong City');
        }
        $school = new Schule();

        $form = $this->createFormBuilder($school)
            ->add('name', TextType::class,['label'=>'Name der Schule','translation_domain' => 'form'])
            ->add('adresse', TextType::class,['label'=>'Straße','translation_domain' => 'form'])
            ->add('adresszusatz', TextType::class,['required'=>false,'label'=>'Adresszusatz','translation_domain' => 'form'])
            ->add('plz', TextType::class,['label'=>'PLZ','translation_domain' => 'form'])
            ->add('ort', TextType::class,['label'=>'Stadt','translation_domain' => 'form'])
            ->add('organisation', EntityType::class, [
                'choice_label' => 'name',
                'class' => Organisation::class,
                'choices' => $organisations,
            ])
            ->add('imageFile', VichImageType::class, [
                'required' => false,
                'allow_delete' => true,
                'delete_label' => 'Löschen',
                'label'=>'Logo hochladen',
                'translation_domain' => 'form'
            ])
            ->add('submit', SubmitType::class, ['label' => 'Speichern','translation_domain' => 'form'])
            ->getForm();
        $form->handleRequest($request);
        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $school = $form->getData();
            $school->setStadt($city);
            $errors = $validator->validate($school);
            if(count($errors)== 0) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($school);
                $em->flush();
                return $this->redirectToRoute('city_admin_schule_show',array('id'=>$city->getId()));
            }

        }
        $title = $translator->trans('Schule anlegen');
        return $this->render('administrator/neu.html.twig',array('title'=>$title,'stadt'=>$city,'form' => $form->createView(),'errors'=>$errors));

    }
    /**
     * @Route("/org_shool/edit", name="city_admin_schule_edit",methods={"GET","POST"})
     */
    public function editSchool(Request $request, ValidatorInterface $validator,TranslatorInterface $translator)
    {
        $school = $this->getDoctrine()->getRepository(Schule::class)->find($request->get('id'));
        $city = $school->getStadt();
        $organisations = $this->getDoctrine()->getRepository(Organisation::class)->findBy(array('stadt'=>$city));

        if($city != $this->getUser()->getStadt()){
            throw new \Exception('Wrong City');
        }


        $form = $this->createFormBuilder($school)
            ->add('name', TextType::class,['label'=>'Name der Schule','translation_domain' => 'form'])
            ->add('adresse', TextType::class,['label'=>'Straße','translation_domain' => 'form'])
            ->add('adresszusatz', TextType::class,['required'=>false,'label'=>'Adresszusatz','translation_domain' => 'form'])
            ->add('plz', TextType::class,['label'=>'PLZ','translation_domain' => 'form'])
            ->add('ort', TextType::class,['label'=>'Stadt','translation_domain' => 'form'])
            ->add('organisation', EntityType::class, [
                'choice_label' => 'name',
                'class' => Organisation::class,
                'choices' => $organisations,
            ])
            ->add('imageFile', VichImageType::class, [
                'required' => false,
                'allow_delete' => true,
                'delete_label' => 'Löschen',
                'label'=>'Logo hochladen',
                'translation_domain' => 'form'
            ])
            ->add('submit', SubmitType::class, ['label' => 'Speichern','translation_domain' => 'form'])
            ->getForm();
        $form->handleRequest($request);
        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $school = $form->getData();
            $errors = $validator->validate($school);
            if(count($errors)== 0) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($school);
                $em->flush();
                return $this->redirectToRoute('city_admin_schule_show',array('id'=>$city->getId()));
            }

        }
        $title = $translator->trans('Schule anlegen');
        return $this->render('administrator/neu.html.twig',array('title'=>$title,'stadt'=>$city,'form' => $form->createView(),'errors'=>$errors));

    }
    /**
     * @Route("/city_schule/delete", name="city_admin_schule_delete",methods={"GET"})
     */
    public function deleteSchool(Request $request, ValidatorInterface $validator,TranslatorInterface $translator)
    {
        $school = $this->getDoctrine()->getRepository(Schule::class)->find($request->get('id'));
        $city = $school->getStadt();
        if($city != $this->getUser()->getStadt()){
            throw new \Exception('Wrong City');
        }

        $school->setDeleted(true);
        $em = $this->getDoctrine()->getManager();
        $em->persist($school);
        $em->flush();
        return $this->redirectToRoute('city_admin_schule_show',array('id'=>$city->getId()));
    }
    /**
     * @Route("/org_shool/detail", name="city_admin_schule_detail",methods={"GET"})
     */
    public function detailSchool(Request $request, ValidatorInterface $validator,TranslatorInterface $translator)
    {
        $school = $this->getDoctrine()->getRepository(Schule::class)->find($request->get('id'));
        $city = $school->getStadt();
        if($city != $this->getUser()->getStadt()){
            throw new \Exception('Wrong City');
        }
        return $this->render('cityAdminSchule/schulenDetail.html.twig',array('stadt'=>$city,'schule'=>$school));

    }
}
