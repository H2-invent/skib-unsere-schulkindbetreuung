<?php

namespace App\Controller;

use App\Entity\Active;
use App\Entity\News;
use App\Entity\Schule;
use App\Entity\Stadt;
use App\Form\Type\NewsType;
use App\Form\Type\SchuljahrType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class NewsController extends AbstractController
{
    /**
     * @Route("city_admin/news/show", name="city_admin_news_anzeige")
     */
    public function index(Request $request)
    {
        $stadt = $this->getDoctrine()->getRepository(Stadt::class)->find($request->get('id'));
        if($stadt != $this->getUser()->getStadt()){
            throw new \Exception('Wrong Organisation');
        }
        $activity = $this->getDoctrine()->getRepository(News::class)->findBy(array('stadt'=>$stadt));

        return $this->render('news/news.html.twig', [
            'city' => $stadt,
            'news'=>$activity
        ]);
    }
    /**
     * @Route("city_admin/news/neu", name="city_admin_news_neu")
     */
    public function neu(Request $request,ValidatorInterface $validator, TranslatorInterface $translator)
    {
        $stadt = $this->getDoctrine()->getRepository(Stadt::class)->find($request->get('id'));
        if ($stadt != $this->getUser()->getStadt()) {
            throw new \Exception('Wrong Organisation');
        }
        $activity = new News();
        $activity->setStadt($stadt);
        $form = $this->createForm(NewsType::class, $activity);
        $form->handleRequest($request);

        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $city = $form->getData();
            $errors = $validator->validate($city);
            if(count($errors)== 0) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($city);
                $em->flush();
                $text = $translator->trans('Erfolgreich angelegt');
                return $this->redirectToRoute('city_admin_news_anzeige',array('id'=>$stadt->getId(),'snack'=>$text));
            }

        }
        $title = $translator->trans('News Eintrag erstellt');
        return $this->render('administrator/neu.html.twig',array('title'=>$title,'form' => $form->createView(),'errors'=>$errors));

    }
    /**
     * @Route("city_admin/news/edit", name="city_admin_news_edit")
     */
    public function edit(Request $request,ValidatorInterface $validator, TranslatorInterface $translator)
    {
        $activity =  $this->getDoctrine()->getRepository(News::class)->find($request->get('id'));

        if ($activity->getStadt() != $this->getUser()->getStadt()) {
            throw new \Exception('Wrong Organisation');
        }

        $form = $this->createForm(NewsType::class, $activity);
        $form->handleRequest($request);

        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $city = $form->getData();
            $errors = $validator->validate($city);
            if(count($errors)== 0) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($city);
                $em->flush();
                $text = $translator->trans('Erfolgreich geändert');
                return $this->redirectToRoute('city_admin_news_anzeige',array('id'=>$activity->getStadt()->getId(),'snack'=>$text));
            }

        }
        $title = $translator->trans('News Eintrag bearbeiten');
        return $this->render('administrator/neu.html.twig',array('title'=>$title,'form' => $form->createView(),'errors'=>$errors));

    }
    /**
     * @Route("city_admin/stadtschuljahr/delete", name="city_admin_news_delete")
     */
    public function delete(Request $request,ValidatorInterface $validator, TranslatorInterface $translator)
    {
        $activity =  $this->getDoctrine()->getRepository(News::class)->find($request->get('id'));

        if ($activity->getStadt() != $this->getUser()->getStadt()) {
            throw new \Exception('Wrong Organisation');
        }
        $em = $this->getDoctrine()->getManager();
        $em->remove($activity);
        $em->flush();
        $text = $translator->trans('Erfolgreich gelöscht');
        return $this->redirectToRoute('city_admin_news_anzeige',array('id'=>$activity->getStadt()->getId(),'snack'=>$text));

    }
}
