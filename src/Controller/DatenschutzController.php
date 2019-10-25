<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class DatenschutzController extends AbstractController
{
    /**
     * @Route("/datenschutz", name="datenschutz")
     */
    public function datenschutzAction()
    {

        return $this->render('datenschutz/datenschutz.html.twig');
    }

    /**
     * @Route("/impressum", name="impressum")
     */
    public function impressumAction()
    {

        return $this->render('datenschutz/impressum.html.twig');
    }
}
