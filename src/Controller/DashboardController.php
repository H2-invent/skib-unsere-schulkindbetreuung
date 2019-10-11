<?php

namespace App\Controller;

use App\Entity\Stadt;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index(Request $request)
    {
        $stadt = array();

        return $this->render('landing/start.html.twig');
    }
    /**
     * @Route("/login/dashboard", name="dashboard")
     */
    public function dashboard()
    {
        return $this->render('adminBase.html.twig');
    }
}
