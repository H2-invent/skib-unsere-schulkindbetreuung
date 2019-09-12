<?php

namespace App\Controller;

use App\Entity\Stadt;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class AdministratorController extends AbstractController
{
    /**
     * @Route("/admin/index", name="admin_index")
     */
    public function index()
    {
        return $this->render('administrator/index.html.twig', [
            'controller_name' => 'AdministratorController',
        ]);
    }
    /**
     * @Route("/admin/stadtverwaltung", name="admin_stadt")
     */
    public function stadtverwaltung()
    {
        $city = $this->getDoctrine()->getRepository(Stadt::class)->findAll();
    
        return $this->render('administrator/stadt.html.twig', [
            'city'=>$city
        ]);
    }
}
