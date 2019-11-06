<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class BerichtController extends AbstractController
{
    /**
     * @Route("/city_report/index", name="stadt_bericht_index")
     */
    public function index()
    {
        return $this->render('bericht/index.html.twig', [
            'controller_name' => 'BerichtController',
        ]);
    }
}
