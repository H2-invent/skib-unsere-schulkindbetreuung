<?php

namespace App\Controller;

use App\Entity\Active;
use App\Entity\Kind;
use App\Entity\Organisation;
use App\Entity\Zeitblock;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Contracts\Translation\TranslatorInterface;
use function Doctrine\ORM\QueryBuilder;

class WidgetController extends AbstractController
{
    /**
     * @Route("/org_child/show/widget/kidsToday", name="widget_kids_today")
     */
    public function index(Request $request,TranslatorInterface $translator)
    {
        $organisation = $this->getDoctrine()->getRepository(Organisation::class)->find($request->get('id'));
        if ($organisation != $this->getUser()->getOrganisation()) {
            throw new \Exception('Wrong Organisation');
        }
        $today = (new \DateTime())->format('w');
        if($today== 0){
            $today = 6;
        }else{
            $today = $today-1;
        }

        $stadt = $this->getUser()->getStadt();
        $active = $this->getDoctrine()->getRepository(Active::class)->findActiveSchuljahrFromCity($stadt);
         $qb = $this->getDoctrine()->getRepository(Kind::class)->createQueryBuilder('k')
            ->innerJoin('k.zeitblocks','b')
            ->andWhere('b.wochentag = :wochentag')
             ->andWhere('b.active = :jahr');
            $schulen = $qb->expr()->orX();
             foreach ($organisation->getSchule() as $key=>$data){

                    $schulen->add('b.schule = :schule'.$key);
                         $qb->setParameter('schule'.$key,$data);
             };
             $qb->andWhere($schulen);
             $qb->andWhere('k.fin = true');


            $qb->setParameter('jahr',$active)
                ->setParameter('wochentag',$today);
        $query = $qb->getQuery();
        $kinder = $result = $query->getResult();


        return new JsonResponse(array('title'=>$translator->trans('Anwesende Kinder heute'),'anzahl'=>sizeof($kinder),'symbol'=>'emoji_people'));
    }

    /**
     * @Route("/org_child/show/widget/kidsSchuljahr", name="widget_kids_schuljahr")
     */
    public function schuljahr(Request $request,TranslatorInterface $translator)
    {
        $organisation = $this->getDoctrine()->getRepository(Organisation::class)->find($request->get('id'));
        if ($organisation != $this->getUser()->getOrganisation()) {
            throw new \Exception('Wrong Organisation');
        }
        $today = (new \DateTime())->format('w');
        if($today== 0){
            $today = 6;
        }else{
            $today = $today-1;
        }

        $stadt = $this->getUser()->getStadt();
        $active = $this->getDoctrine()->getRepository(Active::class)->findActiveSchuljahrFromCity($stadt);
        $qb = $this->getDoctrine()->getRepository(Kind::class)->createQueryBuilder('k')
            ->innerJoin('k.zeitblocks','b')

            ->andWhere('b.active = :jahr');
        $schulen = $qb->expr()->orX();
        foreach ($organisation->getSchule() as $key=>$data){

            $schulen->add('b.schule = :schule'.$key);
            $qb->setParameter('schule'.$key,$data);
        };
        $qb->andWhere($schulen);
        $qb->andWhere('k.fin = true');
        $qb->setParameter('jahr',$active);

        $query = $qb->getQuery();
        $kinder = $result = $query->getResult();


        return new JsonResponse(array('title'=>$translator->trans('Kinder dieses Schuljahr'),'anzahl'=>sizeof($kinder),'symbol'=>'emoji_people'));
    }
}
