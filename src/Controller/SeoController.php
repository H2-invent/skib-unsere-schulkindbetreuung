<?php

namespace App\Controller;

use App\Entity\Content;
use App\Entity\Stadt;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class SeoController extends AbstractController
{
        public function __construct(private \Doctrine\Persistence\ManagerRegistry $managerRegistry)
        {
        }
        /**
         * @Route("/sitemap.xml", name="sitemap")
         */
        public function index()
    {
        $stadt = $this->managerRegistry->getRepository(Stadt::class)->findBy(array('active'=>true,'deleted'=>false));
        $content = $this->managerRegistry->getRepository(Content::class)->findBy(array('activ'=>true));
        $res = $this->render('seo/index.xml.twig', [
        'stadt'=>$stadt,
            'content'=>$content
        ]);
		 $res->headers->set('Content-Type', 'text/xml');
		 return $res;
    }
    /**
     * @Route("/robots.txt", name="robots")
     */
    public function robots()
    {
             $res = $this->render('seo/robots.html.twig');
        $res->headers->set('Content-Type', 'text/plain');
        return $res;
    }
}
