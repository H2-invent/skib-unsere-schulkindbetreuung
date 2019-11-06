<?php

namespace App\Controller;

use App\Entity\Active;
use App\Entity\Kind;
use App\Entity\Schule;
use App\Entity\Stadt;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class BerichtController extends AbstractController
{
    /**
     * @Route("/city_report/index", name="stadt_bericht_index")
     */
    public function index(Request $request)
    {
        $stadt = $this->getDoctrine()->getRepository(Stadt::class)->find($request->get('stadt_id'));
        if ($stadt != $this->getUser()->getStadt()) {
            throw new \Exception('Wrong City');
        }

        $qb = $this->getDoctrine()->getRepository(Kind::class)->createQueryBuilder('k')
            ->innerJoin('k.zeitblocks','b');

        //$qb = $repo->createQueryBuilder('b');
         $blocks = array();

            foreach ($stadt->getSchules() as $key=>$data){
                $qb->orWhere('b.schule = :schule'.$key)
                    ->setParameter('schule'.$key,$data);
            }

        if($request->get('schuljahr')){
            $jahr = $this->getDoctrine()->getRepository(Active::class)->find($request->get('schuljahr'));
            $qb->andWhere('b.active = :jahr')
                ->setParameter('jahr',$jahr);
        }

        $qb->andWhere('k.fin = 1');
        $query = $qb->getQuery();
        $blocks = $result = $query->getResult();
        $kinder = $blocks;
        dump($blocks);
        return $this->render('bericht/index.html.twig',array('kinder'=>$kinder));
    }
}
