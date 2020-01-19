<?php

namespace App\Controller;

use App\Entity\Organisation;
use App\Entity\Stadt;
use App\Form\Type\OrganisationType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class OrganisationController extends AbstractController
{
    /**
     * @Route("/city_admin/organisation/show", name="city_admin_organisation_show")
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
     * @Route("/city_admin/organisation/new", name="city_admin_organisation_new",methods={"GET","POST"})
     */
    public function newSchool(Request $request, ValidatorInterface $validator,TranslatorInterface $translator)
    {
        $city = $this->getDoctrine()->getRepository(Stadt::class)->find($request->get('id'));
        if($city != $this->getUser()->getStadt()){
            throw new \Exception('Wrong City');
        }
        $organisation = new Organisation();
        $form = $this->createForm(OrganisationType::class, $organisation);
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
                $text = $translator->trans('Erfolgreich angelegt');
                return $this->redirectToRoute('city_admin_organisation_show',array('snack'=>$text,'id'=>$city->getId()));
            }

        }
        $title = $translator->trans('Organisation anlegen');
        return $this->render('administrator/neu.html.twig',array('title'=>$title,'stadt'=>$city,'form' => $form->createView(),'errors'=>$errors));

    }
    /**
     * @Route("/org_edit/organisation/edit", name="city_admin_organisation_edit",methods={"GET","POST"})
     */
    public function editSchool(Request $request, ValidatorInterface $validator,TranslatorInterface $translator)
    {

        $organisation = $this->getDoctrine()->getRepository(Organisation::class)->find($request->get('id'));

        if($organisation->getStadt() != $this->getUser()->getStadt() && $this->getUser()->getOrganisation()!= $organisation){
            throw new \Exception('Wrong City');
        }

        $form = $this->createForm(OrganisationType::class, $organisation);
        if($organisation->getStadt()->getFerienprogramm() === false){
            $form->remove('ferienprogramm');
        }
        $form->handleRequest($request);
        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $organisation = $form->getData();
            $errors = $validator->validate($organisation);
            if(count($errors)== 0) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($organisation);
                $em->flush();
                $text = $translator->trans('Erfolgreich geändert');
                return $this->redirectToRoute('city_admin_organisation_detail',array('snack'=>$text,'id'=>$organisation->getId()));
            }

        }
        $title = $translator->trans('Organisation anlegen');
        return $this->render('administrator/neu.html.twig',array('title'=>$title,'form' => $form->createView(),'errors'=>$errors));

    }
    /**
     * @Route("/city_admin/organisation/delete", name="city_admin_organisation_delete",methods={"GET"})
     */
    public function deleteSchool(Request $request, ValidatorInterface $validator,TranslatorInterface $translator)
    {
        $organisation = $this->getDoctrine()->getRepository(Organisation::class)->find($request->get('id'));
        $city = $organisation->getStadt();
        if($organisation->getStadt() != $this->getUser()->getStadt()){
            throw new \Exception('Wrong City');
        }

        $organisation->setDeleted(true);
        $em = $this->getDoctrine()->getManager();
        $em->persist($organisation);
        $em->flush();
        $text = $translator->trans('Erfolgreich gelöscht');
        return $this->redirectToRoute('city_admin_organisation_show',array('snack'=>$text,'id'=>$city->getId()));
    }
    /**
     * @Route("/org_edit/organisation/detail", name="city_admin_organisation_detail",methods={"GET"})
     */
    public function detailSchool(Request $request, ValidatorInterface $validator,TranslatorInterface $translator)
    {
        $organisation = $this->getDoctrine()->getRepository(Organisation::class)->find($request->get('id'));
        $city = $organisation->getStadt();
        if($organisation->getStadt() != $this->getUser()->getStadt() && $this->getUser()->getOrganisation()!= $organisation){
            throw new \Exception('Wrong City');
        }
        return $this->render('cityAdminOrganisation/organisationDetail.html.twig',array('stadt'=>$city,'organisation'=>$organisation));

    }

}
