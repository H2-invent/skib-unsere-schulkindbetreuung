<?php
namespace App\Controller;
/**
 * Created by PhpStorm.
 * User: Emanuel
 * Date: 06.09.2019
 * Time: 12:21
 */

use App\Entity\Schule;
use App\Entity\Stammdaten;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Validator\Validator\ValidatorInterface;
class workflowController  extends AbstractController
{
    /**
    * @Route("/{stadt}/start",name="workflow_start",methods={"GET"})
    */
    public function welcomeAction(Request $request, $stadt)
    {

       $city = $this->getDoctrine()->getRepository(Schule::class)->findOneBy(array('slug'=>$stadt));

        return $this->render('workflow/start.html.twig',array('stadt'=>$city));
    }
    /**
     * @Route("/{stadt}/adresse",name="workflow_adresse",methods={"GET","POST"})
     */
    public function adresseAction(Request $request, $stadt,ValidatorInterface $validator)
    {
        $adresse = new Stammdaten;
        $adresse->setCreatedAt(new \DateTime());
        $city = $this->getDoctrine()->getRepository(Schule::class)->findOneBy(array('slug'=>$stadt));
        $form = $this->createFormBuilder($adresse)
            ->add('name', TextType::class)
            ->add('vorname', TextType::class)
            ->add('strasse', TextType::class)
            ->add('adresszusatz', TextType::class)
            ->add('einkommen', NumberType::class)
            ->add('submit', SubmitType::class, ['label' => 'Speichern'])
            ->getForm();
        $form->handleRequest($request);
        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $adresse = $form->getData();
            $errors = $validator->validate($adresse);
            if(count($errors)== 0) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($adresse);
                $em->flush();
            }
           // return $this->redirectToRoute('task_success');
        }

        return $this->render('workflow/adresse.html.twig',array('stadt'=>$city,'form' => $form->createView(),'errors'=>$errors));
    }

}