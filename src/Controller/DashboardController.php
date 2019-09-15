<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index()
    {
        return $this->redirectToRoute('dashboard');
    }
    /**
     * @Route("/login/dashboard", name="dashboard")
     */
    public function dashboard()
    {
        return $this->render('adminBase.html.twig');
    }
}
