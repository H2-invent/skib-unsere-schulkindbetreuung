<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class LegalController extends AbstractController
{
    #[Route(path: '/datenschutz', name: 'datenschutz')]
    public function privacyAction()
    {
        return $this->render('legal/datenschutz.html.twig');
    }

    #[Route(path: '/impressum', name: 'impressum')]
    public function impressumAction()
    {
        return $this->render('legal/impressum.html.twig');
    }

    #[Route(path: '/nutzungsbedingungen', name: 'nutzungsbedingungen')]
    public function nutzungsbedingungenAction()
    {
        return $this->render('legal/nutzungsbedingungen.html.twig');
    }
}
