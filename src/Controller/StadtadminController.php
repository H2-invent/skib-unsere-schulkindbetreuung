<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class StadtadminController extends AbstractController
{
    /**
     * @Route("/admin/stadtUser", name="admin_stadtadmin")
     */
    public function index()
    {
        return $this->render('stadtadmin/index.html.twig', [
            'controller_name' => 'StadtadminController',
        ]);
    }
}
