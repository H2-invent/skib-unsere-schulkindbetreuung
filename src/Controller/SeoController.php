<?php

namespace App\Controller;

use App\Entity\Stadt;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class SeoController extends AbstractController
{
    /**
     * @Route("/sitemap.xml", name="seo")
     */
    public function index()
    {
        $stadt = $this->getDoctrine()->getRepository(Stadt::class)->findBy(array('active'=>true,'deleted'=>false));

        return $this->render('seo/index.xml.twig', [
        'stadt'=>$stadt
        ]);
    }
}
