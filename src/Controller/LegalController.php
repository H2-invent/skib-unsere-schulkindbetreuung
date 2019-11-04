<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class LegalController extends AbstractController
{
    /**
     * @Route("/datenschutz", name="datenschutz")
     */
    public function privacyAction()
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

    /**
     * @Route("/nutzungsbedingungen", name="nutzungsbedingungen")
     */
    public function nutzungsbedingungenAction()
    {

        return $this->render('legal/nutzungsbedingungen.html.twig');
    }
}
