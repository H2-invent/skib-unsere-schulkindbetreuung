<?php

namespace App\Controller;

use App\Entity\Active;
use App\Entity\Kind;
use App\Entity\Organisation;
use App\Entity\Schule;
use App\Entity\Stadt;
use App\Entity\Zeitblock;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Contracts\Translation\TranslatorInterface;

class WidgetStadtController extends AbstractController
{
    /**
     * @Route("/city_dashboard/show/widget/kidsinSchule", name="widget_kids_schule_stadt")
     */
    public function childsInSchule(Request $request,TranslatorInterface $translator)
    {
        $stadt = $this->getDoctrine()->getRepository(Stadt::class)->find($request->get('stadt_id'));
        if ($stadt != $this->getUser()->getStadt()) {
            throw new \Exception('Wrong City');
        }
        $today = (new \DateTime())->format('w');
        $stadt = $this->getUser()->getStadt();
        $active = $this->getDoctrine()->getRepository(Active::class)->findActiveSchuljahrFromCity($stadt);
        $schule = $this->getDoctrine()->getRepository(Schule::class)->findOneBy(array('stadt'=>$stadt,'id'=>$request->get('schule_id')));
        $qb = $this->getDoctrine()->getRepository(Kind::class)->createQueryBuilder('k')
            ->innerJoin('k.zeitblocks','b')
            ->andWhere('b.active = :jahr')
            ->andWhere('b.schule = :schule')
            ->setParameter('schule',$schule);

        $qb->andWhere('k.fin = true');
        $qb->setParameter('jahr',$active);

        $query = $qb->getQuery();
        $kinder = $result = $query->getResult();
        return new JsonResponse(array('title'=>$schule->getName(),'small'=>$translator->trans('Kinder angemeldet'),'anzahl'=>sizeof($kinder),'symbol'=>'sports_handball'));

    }
    /**
     * @Route("/city_dashboard/show/widget/kidsSchuljahr", name="widget_kids_schuljahr_stadt")
     */
    public function schuljahr(Request $request,TranslatorInterface $translator)
    {
        $stadt = $this->getDoctrine()->getRepository(Stadt::class)->find($request->get('stadt_id'));
        if ($stadt != $this->getUser()->getStadt()) {
            throw new \Exception('Wrong City');
        }
        $today = (new \DateTime())->format('w');
        if($today== 0){
            $today = 6;
        }else{
            $today = $today-1;
        }

        $active = $this->getDoctrine()->getRepository(Active::class)->findActiveSchuljahrFromCity($stadt);
        $qb = $this->getDoctrine()->getRepository(Kind::class)->createQueryBuilder('k')
            ->innerJoin('k.zeitblocks','b')

            ->andWhere('b.active = :jahr');
        $schulen = $qb->expr()->orX();
        foreach ($stadt->getSchules() as $key=>$data){

            $schulen->add('b.schule = :schule'.$key);
            $qb->setParameter('schule'.$key,$data);
        };
        $qb->andWhere($schulen);
        $qb->andWhere('k.fin = true');
        $qb->setParameter('jahr',$active);

        $query = $qb->getQuery();
        $kinder = $result = $query->getResult();


        return new JsonResponse(array('title'=>$translator->trans('Kinder dieses Schuljahr'),'small'=>'','anzahl'=>sizeof($kinder),'symbol'=>'emoji_people'));
    }

    function cmp($a, $b)
    {
        if ($a['active']->getVon() == $b['active']->getVon()) {
            return 0;
        }
        return ($a['active']->getVon() < $b['active']->getVon()) ? -1 : 1;
    }

    /**
     * @Route("/city_dashboard/show/widget/kidsOverYears", name="widget_stadt_over_years")
     */
    public function overYears(Request $request,TranslatorInterface $translator)
    {
        $stadt = $this->getDoctrine()->getRepository(Stadt::class)->find($request->get('stadt_id'));
        if ($stadt != $this->getUser()->getStadt()) {
            throw new \Exception('Wrong City');
        }
        $active = $this->getDoctrine()->getRepository(Active::class)->findBy(array('stadt'=>$stadt));
        $kinder = array();
        foreach ($active as $data){
            $qb = $this->getDoctrine()->getRepository(Kind::class)->createQueryBuilder('k')
                ->innerJoin('k.zeitblocks','b')

                ->andWhere('b.active = :jahr');
            $schulen = $qb->expr()->orX();
            foreach ($stadt->getSchules() as $key=>$data2){

                $schulen->add('b.schule = :schule'.$key);
                $qb->setParameter('schule'.$key,$data2);
            };
            $qb->andWhere($schulen);
            $qb->andWhere('k.fin = true');
            $qb->setParameter('jahr',$data);

            $query = $qb->getQuery();
            $kinder[] = array('active'=>$data,'kinder'=>sizeof($result = $query->getResult()));
        }

        usort($kinder, function ($a, $b)
        {
            if ($a['active']->getVon() == $b['active']->getVon()) {
                return 0;
            }
            return ($a['active']->getVon() < $b['active']->getVon()) ? -1 : 1;
        });


      return $this->render('widget_stadt/chartKids.twig',array('kinder'=>$kinder));

    }
    /**
     * @Route("/city_dashboard/show/widget/kidsinblocks", name="widget_stadt_kids_in_blocks")
     */
    public function kidsinBlocks(Request $request,TranslatorInterface $translator)
    {
        $stadt = $this->getDoctrine()->getRepository(Stadt::class)->find($request->get('stadt_id'));
        if ($stadt != $this->getUser()->getStadt()) {
            throw new \Exception('Wrong City');
        }
        $today = new DateTime();
        $result = array();
        $active = $this->getDoctrine()->getRepository(Active::class)->findActiveSchuljahrFromCity($stadt);
        foreach ($stadt->getOrganisations() as $data){

                $zb = array();
            foreach ($data->getSchule() as $data2){
                $zb[] = array('schule'=>$data2,'blocks'=>$this->getDoctrine()->getRepository(Zeitblock::class)->findBy(array('deleted'=>false,'schule'=>$data2,'active'=>$active)));
            }
            $result[] = array('org'=>$data,'schule'=>$zb);
        }


        return $this->render('widget_stadt/orgs.html.twig',array('stadt'=>$stadt,'result'=>$result));
    }

}
