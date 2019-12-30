<?php

namespace App\Controller;

use App\Entity\Stadt;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class LandingController extends AbstractController
{
    /**
     * @Route("/", name="welcome_landing")
     */
    public function welcomeAction(Request $request)
    {
        $stadt = $this->getDoctrine()->getRepository(Stadt::class)->findBy(array('deleted'=>false, 'active'=>true));
     
        return $this->render('landing/landing.html.twig',array('stadt'=>$stadt));
    }
}
