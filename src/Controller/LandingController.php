<?php

namespace App\Controller;

use App\Entity\Stadt;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class LandingController extends AbstractController
{
    /**
     * @Route("/", name="welcome_landing")
     */
    public function welcomeAction(TranslatorInterface $translator, Request $request)
    {
        $stadt = $this->getDoctrine()->getRepository(Stadt::class)->findBy(array('deleted'=>false, 'active'=>true));
        $title = $translator->trans('Schulkindbetreuung').' | '.$translator->trans('Online Anmeldung und Verwaltung');
        return $this->render('landing/landing.html.twig',array('title'=>$title,'stadt'=>$stadt));
    }
}
