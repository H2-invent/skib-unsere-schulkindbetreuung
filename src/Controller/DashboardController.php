<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{

    /**
     * @Route("/login/dashboard", name="dashboard")
     */
    public function dashboard()
    {
        return $this->render('dashboard/index.html.twig');
    }
}
