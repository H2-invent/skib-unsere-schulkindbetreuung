<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class LegalController extends AbstractController
{
    /**
     * @Route("/privacy", name="datenschutz")
     */
    public function datenschutzAction()
    {

        return $this->render('legal/datenschutz.html.twig');
    }

    /**
     * @Route("/impressum", name="impressum")
     */
    public function impressumAction()
    {

        return $this->render('legal/impressum.html.twig');
    }
}
