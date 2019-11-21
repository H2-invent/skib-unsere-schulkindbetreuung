<?php

namespace App\Controller;

use App\Entity\Active;
use App\Entity\Ferienblock;
use App\Entity\Kind;
use App\Entity\News;
use App\Entity\Organisation;
use App\Entity\Schule;
use App\Entity\Stadt;
use App\Entity\Stammdaten;
use App\Form\Type\FerienBlockType;
use App\Form\Type\LoerrachKind;
use App\Form\Type\NewsType;
use App\Form\Type\SchuljahrType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class FerienVerwaltungController extends AbstractController
{
    /**
     * @Route("org_admin/ferien/show", name="org_admin_ferien_anzeige")
     */
    public function index(Request $request)
    {
        $organisation = $this->getDoctrine()->getRepository(Organisation::class)->find($request->get('id'));
        $city = $organisation->getStadt();
        if($organisation->getStadt() != $this->getUser()->getStadt()){
            throw new \Exception('Wrong City');
        }

        $ferienblocks = $this->getDoctrine()->getRepository(Ferienblock::class)->findBy(array('organisation'=>$organisation));

        return $this->render('ferienOrganisation/ferienVerwaltung.html.twig', [
            'org' => $organisation,
            'ferienblocks'=> $ferienblocks
        ]);
    }


    /**
     * @Route("org_admin/ferien/neu", name="org_admin_ferien_neu")
     */
    public function neu(Request $request,ValidatorInterface $validator, TranslatorInterface $translator)
    {
        $organisation = $this->getDoctrine()->getRepository(Organisation::class)->find($request->get('id'));
        $city = $organisation->getStadt();
        if($organisation->getStadt() != $this->getUser()->getStadt()){
            throw new \Exception('Wrong City');
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
                return $this->redirectToRoute('org_admin_ferien_anzeige',array('id'=>$organisation->getId(),'snack'=>$text));
            }

        }
        $title = $translator->trans('Ferienprogramm erstellt');
        return $this->render('administrator/neu.html.twig',array('title'=>$title,'form' => $form->createView(),'errors'=>$errors));

    }


    /**
     * @Route("city_admin/news/edit", name="city_admin_news_edit")
     */
    public function edit(Request $request,ValidatorInterface $validator, TranslatorInterface $translator)
    {
        $activity =  $this->getDoctrine()->getRepository(News::class)->find($request->get('id'));

        if ($activity->getStadt() != $this->getUser()->getStadt()) {
            throw new \Exception('Wrong City');
        }

        $today = new \DateTime();
        $activity->setDate($today);
        $form = $this->createForm(NewsType::class, $activity);
        $form->handleRequest($request);

        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $news = $form->getData();
            $errors = $validator->validate($news);
            if(count($errors)== 0) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($news);
                $em->flush();
                $text = $translator->trans('Erfolgreich geändert');
                return $this->redirectToRoute('city_admin_news_anzeige',array('id'=>$activity->getStadt()->getId(),'snack'=>$text));
            }

        }
        $title = $translator->trans('News Eintrag bearbeiten');
        return $this->render('administrator/neu.html.twig',array('title'=>$title,'form' => $form->createView(),'errors'=>$errors));

    }


    /**
     * @Route("city_admin/news/delete", name="city_admin_news_delete")
     */
    public function delete(Request $request,ValidatorInterface $validator, TranslatorInterface $translator)
    {
        $activity =  $this->getDoctrine()->getRepository(News::class)->find($request->get('id'));

        if ($activity->getStadt() != $this->getUser()->getStadt()) {
            throw new \Exception('Wrong City');
        }
        $em = $this->getDoctrine()->getManager();
        $em->remove($activity);
        $em->flush();
        $text = $translator->trans('Erfolgreich gelöscht');
        return $this->redirectToRoute('city_admin_news_anzeige',array('id'=>$activity->getStadt()->getId(),'snack'=>$text));
    }


    /**
     * @Route("city_admin/news/deactivate", name="city_admin_news_deactivate")
     */
    public function deactivateAction(Request $request,ValidatorInterface $validator, TranslatorInterface $translator)
    {
        $news =  $this->getDoctrine()->getRepository(News::class)->find($request->get('id'));

        if ($news->getStadt() != $this->getUser()->getStadt()) {
            throw new \Exception('Wrong City');
        }
        $news->setActiv(false);
        $em = $this->getDoctrine()->getManager();
        $em->persist($news);
        $em->flush();
        $text = $translator->trans('Erfolgreich deaktiviert');
        return $this->redirectToRoute('city_admin_news_anzeige',array('id'=>$news->getStadt()->getId(),'snack'=>$text));
    }


    /**
     * @Route("city_admin/news/activate", name="city_admin_news_activate")
     */
    public function activateAction(Request $request,ValidatorInterface $validator, TranslatorInterface $translator)
    {
        $news =  $this->getDoctrine()->getRepository(News::class)->find($request->get('id'));

        if ($news->getStadt() != $this->getUser()->getStadt()) {
            throw new \Exception('Wrong City');
        }
        $today = new \DateTime();
        $news->setActiv(true);
        $news->setDate($today);

        $em = $this->getDoctrine()->getManager();
        $em->persist($news);
        $em->flush();
        $text = $translator->trans('Erfolgreich aktiviert');
        return $this->redirectToRoute('city_admin_news_anzeige',array('id'=>$news->getStadt()->getId(),'snack'=>$text));
    }


}
