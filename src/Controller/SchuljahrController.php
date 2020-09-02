<?php

namespace App\Controller;

use App\Entity\Active;
use App\Entity\Schule;
use App\Entity\Stadt;
use App\Form\Type\SchuljahrType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class SchuljahrController extends AbstractController
{
    /**
     * @Route("city_admin/stadtschuljahr/show", name="city_admin_schuljahr_anzeige")
     */
    public function index(Request $request)
    {
        $stadt = $this->getDoctrine()->getRepository(Stadt::class)->find($request->get('id'));
        if($stadt != $this->getUser()->getStadt()){
            throw new \Exception('Wrong Organisation');
        }
        $activity = $this->getDoctrine()->getRepository(Active::class)->findBy(array('stadt'=>$stadt));

        return $this->render('schuljahr/schuljahre.html.twig', [
            'city' => $stadt,
            'schuljahre'=>$activity
        ]);
    }
    /**
     * @Route("city_admin/stadtschuljahr/neu", name="city_admin_schuljahr_neu")
     */
    public function neu(Request $request,ValidatorInterface $validator, TranslatorInterface $translator)
    {
        $stadt = $this->getDoctrine()->getRepository(Stadt::class)->find($request->get('id'));
        if ($stadt != $this->getUser()->getStadt()) {
            throw new \Exception('Wrong Organisation');
        }
        $activity = new Active();
        $activity->setStadt($stadt);
        $form = $this->createForm(SchuljahrType::class, $activity);
        $form->handleRequest($request);

        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $activity = $form->getData();

            $errors = $validator->validate($activity);
            if(count($errors)== 0) {
                $activity->setAnmeldeEnde($activity->getAnmeldeEnde()->setTime(23,59,59));
                $em = $this->getDoctrine()->getManager();
                $em->persist($activity);
                $em->flush();
                $text = $translator->trans('Erfolgreich angelegt');
                return $this->redirectToRoute('city_admin_schuljahr_anzeige',array('id'=>$stadt->getId(),'snack'=>$text));
            }

        }
        $title = $translator->trans('Schuljahr anlegen');
        return $this->render('administrator/neu.html.twig',array('title'=>$title,'form' => $form->createView(),'errors'=>$errors));

    }
    /**
     * @Route("city_admin/stadtschuljahr/edit", name="city_admin_schuljahr_edit")
     */
    public function edit(Request $request,ValidatorInterface $validator, TranslatorInterface $translator)
    {
        $activity =  $this->getDoctrine()->getRepository(Active::class)->find($request->get('id'));

        if ($activity->getStadt() != $this->getUser()->getStadt()) {
            throw new \Exception('Wrong Organisation');
        }

        $form = $this->createForm(SchuljahrType::class, $activity);
        $form->handleRequest($request);

        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $activity = $form->getData();
            $errors = $validator->validate($activity);
            if(count($errors)== 0) {
                $activity->setAnmeldeEnde($activity->getAnmeldeEnde()->setTime(23,59,59));
                $em = $this->getDoctrine()->getManager();
                $em->persist($activity);
                $em->flush();
                $text = $translator->trans('Erfolgreich geändert');
                return $this->redirectToRoute('city_admin_schuljahr_anzeige',array('id'=>$activity->getStadt()->getId(),'snack'=>$text));
            }

        }
        $title = $translator->trans('Schuljahr bearbeiten');
        return $this->render('administrator/neu.html.twig',array('title'=>$title,'form' => $form->createView(),'errors'=>$errors));

    }
    /**
     * @Route("city_admin/stadtschuljahr/delete", name="city_admin_schuljahr_delete")
     */
    public function delete(Request $request,ValidatorInterface $validator, TranslatorInterface $translator)
    {
        $activity =  $this->getDoctrine()->getRepository(Active::class)->find($request->get('id'));

        if ($activity->getStadt() != $this->getUser()->getStadt()) {
            throw new \Exception('Wrong Organisation');
        }
        $em = $this->getDoctrine()->getManager();
        $em->remove($activity);
        $em->flush();
        $text = $translator->trans('Erfolgreich gelöscht');
        return $this->redirectToRoute('city_admin_schuljahr_anzeige',array('id'=>$activity->getStadt()->getId(),'snack'=>$text));

    }
}
