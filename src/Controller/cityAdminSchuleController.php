<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class cityAdminSchuleController extends AbstractController
{
    /**
     * @Route("/city/admin/schule/show", name="city_admin_schule_show")
     */
    public function index()
    {
        return $this->render('cityAdminSchule/index.html.twig', [
            'controller_name' => 'SchuleController',
        ]);
    }
}
