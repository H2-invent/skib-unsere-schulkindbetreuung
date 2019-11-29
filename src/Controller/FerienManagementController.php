<?php

namespace App\Controller;

use App\Entity\Ferienblock;
use App\Entity\KindFerienblock;
use App\Entity\News;
use App\Entity\Organisation;
use App\Form\Type\FerienBlockPreisType;
use App\Form\Type\FerienBlockType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class FerienManagementController extends AbstractController
{
    /**
     * @Route("/org_ferien/edit/show", name="ferien_management_show",methods={"GET"})
     */
    public function index(Request $request)
    {

        $organisation = $this->getDoctrine()->getRepository(Organisation::class)->find($request->get('org_id'));
        if ($organisation != $this->getUser()->getOrganisation()) {
            throw new \Exception('Wrong Organisation');
        }
        $blocks = $this->getDoctrine()->getRepository(Ferienblock::class)->findBy(array('organisation'=>$organisation),array('startDate'=>'asc'));

        return $this->render('ferien_management/index.html.twig',array('blocks'=>$blocks,'org'=>$organisation));
    }


    /**
     * @Route("/org_ferien/edit/neu", name="ferien_management_neu", methods={"GET","POST"})
     */
    public function neu(Request $request,ValidatorInterface $validator, TranslatorInterface $translator)
    {
        $organisation = $this->getDoctrine()->getRepository(Organisation::class)->find($request->get('org_id'));
          if($organisation != $this->getUser()->getOrganisation()){
            throw new \Exception('Wrong Organisation');
        }
        $ferienblock = new Ferienblock();
        $ferienblock->setOrganisation($organisation);
        $ferienblock->setStadt($organisation->getStadt());
        $form = $this->createForm(FerienBlockType::class, $ferienblock);

        $form->handleRequest($request);

        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $block = $form->getData();
            $errors = $validator->validate($block);
            if(count($errors)== 0) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($block);
                $em->flush();
                $text = $translator->trans('Erfolgreich angelegt');
                return $this->redirectToRoute('ferien_management_preise',array('ferien_id'=>$block->getId(),'org_id'=>$organisation->getId(),'snack'=>$text));
            }

        }
        $title = $translator->trans('Ferienprogramm erstellen');
        return $this->render('administrator/neu.html.twig',array('title'=>$title,'form' => $form->createView(),'errors'=>$errors));

    }


    /**
     * @Route("/org_ferien/edit/preise", name="ferien_management_preise", methods={"GET","POST"})
     */
    public function preise(Request $request,ValidatorInterface $validator, TranslatorInterface $translator)
    {
        $organisation = $this->getDoctrine()->getRepository(Organisation::class)->find($request->get('org_id'));
        if($organisation != $this->getUser()->getOrganisation()){
            throw new \Exception('Wrong Organisation');
        }
        $ferienblock = $this->getDoctrine()->getRepository(Ferienblock::class)->findOneBy(array('id'=>$request->get('ferien_id'),'organisation'=>$organisation));
        if($ferienblock->getPreis() === null || $ferienblock->getNamePreise() === null || sizeof($ferienblock->getPreis()) != $ferienblock->getAnzahlPreise()){
            $ferienblock->setNamePreise(array_fill(0,$ferienblock->getAnzahlPreise(), ''));
            $ferienblock->setPreis(array_fill(0,$ferienblock->getAnzahlPreise(), 0));
        }

        $form = $this->createForm(FerienBlockPreisType::class, $ferienblock);
        $form->handleRequest($request);
        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $block = $form->getData();
            $errors = $validator->validate($block);
            if(count($errors)== 0) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($block);
                $em->flush();
                $text = $translator->trans('Erfolgreich geändert');
                return $this->redirectToRoute('ferien_management_show',array('org_id'=>$organisation->getId(),'snack'=>$text));
            }

        }
        $title = $translator->trans('Preise bearbeiten');
        return $this->render('administrator/neu.html.twig',array('title'=>$title,'form' => $form->createView(),'errors'=>$errors));
    }


    /**
     * @Route("/org_ferien/edit/edit", name="ferien_management_edit", methods={"GET","POST"})
     */
    public function edit(Request $request,ValidatorInterface $validator, TranslatorInterface $translator)
    {
        $organisation = $this->getDoctrine()->getRepository(Organisation::class)->find($request->get('org_id'));
         if($organisation != $this->getUser()->getOrganisation()){
            throw new \Exception('Wrong Organisation');
        }

        $ferienblock = $this->getDoctrine()->getRepository(Ferienblock::class)->findOneBy(array('id'=>$request->get('ferien_id'),'organisation'=>$organisation));

        $form = $this->createForm(FerienBlockType::class, $ferienblock);

        $form->handleRequest($request);

        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $block = $form->getData();
            $errors = $validator->validate($block);
            if(count($errors)== 0) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($block);
                $em->flush();
                $text = $translator->trans('Erfolgreich geändert');
                return $this->redirectToRoute('ferien_management_show',array('org_id'=>$organisation->getId(),'snack'=>$text));
            }

        }
        $title = $translator->trans('Ferienprogramm bearbeiten');
        return $this->render('administrator/neu.html.twig',array('title'=>$title,'form' => $form->createView(),'errors'=>$errors));

    }


    /**
     * @Route("/org_ferien/edit/delete", name="ferien_management_delete", methods={"DELETE"})
     */
    public function delte(Request $request,ValidatorInterface $validator, TranslatorInterface $translator)
    {
        $organisation = $this->getDoctrine()->getRepository(Organisation::class)->find($request->get('org_id'));
            if($organisation != $this->getUser()->getOrganisation()){
            throw new \Exception('Wrong Organisation');
        }

        $ferienblock = $this->getDoctrine()->getRepository(Ferienblock::class)->findOneBy(array('id'=>$request->get('ferien_id'),'organisation'=>$organisation));
        $em = $this->getDoctrine()->getManager();
        $em->remove($ferienblock);
        $em->flush();
        $text = $translator->trans('Erfolgreich gelöscht');
        return new JsonResponse(array('redirect'=>$this->generateUrl('ferien_management_show',array('org_id'=>$organisation->getId(),'snack'=>$text))));
    }


    /**
     * @Route("/org_ferien/duplicate", name="ferien_management_duplicate", methods={"POST"})
     */
    public function duplicate(Request $request,ValidatorInterface $validator, TranslatorInterface $translator)
    {
        $organisation = $this->getDoctrine()->getRepository(Organisation::class)->find($request->get('org_id'));
        if($organisation != $this->getUser()->getOrganisation()){
            throw new \Exception('Wrong Organisation');
        }

        $ferienblock = $this->getDoctrine()->getRepository(Ferienblock::class)->findOneBy(array('id'=>$request->get('ferien_id'),'organisation'=>$organisation));
        $ferienblockNew = clone $ferienblock;
        $translations = $ferienblock->getTranslations();
        foreach ($translations as $locale => $fields) {
            $clone = clone  $fields;
            $clone->setTitel('[copy]'.$clone->getTitel());
           $ferienblockNew->addTranslation($clone);

        }
        $em = $this->getDoctrine()->getManager();
        $em->persist($ferienblockNew);
        $em->flush();
        $text = $translator->trans('Erfolgreich kopiert');
        return new JsonResponse(array('redirect'=>$this->generateUrl('ferien_management_edit',array('org_id'=>$organisation->getId(),'ferien_id'=>$ferienblockNew->getId(),'snack'=>$text))));

    }


    /**
     * @Route("/org_ferien/report/checkinlist", name="ferien_management_report_checkinlist", methods={"GET","POST"})
     */
    public function checkinListFerien(Request $request,ValidatorInterface $validator, TranslatorInterface $translator)
    {
        $organisation = $this->getDoctrine()->getRepository(Organisation::class)->find($request->get('org_id'));
        $block = $this->getDoctrine()->getRepository(Ferienblock::class)->find($request->get('ferien_id'));
        if($organisation != $this->getUser()->getOrganisation()){
            throw new \Exception('Wrong Organisation');
        }

        $list = $this->getDoctrine()->getRepository(KindFerienblock::class)->findOneBy(array('ferienblock'=>$block));

        dump($list);
        return $this->render('ferien_management/checkinList.html.twig', [
            'org' => $organisation,
            'list'=>$list,
            ]);
    }
}
