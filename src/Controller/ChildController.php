<?php

namespace App\Controller;

use App\Entity\Active;
use App\Entity\Kind;
use App\Entity\Organisation;
use App\Entity\Schule;
use App\Entity\Zeitblock;
use App\Service\ChildExcelService;
use App\Service\PrintService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use WhiteOctober\TCPDFBundle\Controller\TCPDFController;
use function Doctrine\ORM\QueryBuilder;

class ChildController extends AbstractController
{
    private $wochentag;
    private $translator;
    public function __construct( TranslatorInterface $translator)
    {
        $this->translator = $translator;
        $this->wochentag = [
            $this->translator->trans('Montag'),
            $this->translator->trans('Dienstag'),
            $this->translator->trans('Mittwoch'),
            $this->translator->trans('Donnerstag'),
            $this->translator->trans('Freitag'),
            $this->translator->trans('Samstag'),
            $this->translator->trans('Sonntag'),
            ];
    }

    /**
     * @Route("/org_child/show", name="child_show")
     */
    public function showAction(Request $request, TranslatorInterface $translator)
    {
        $organisation = $this->getDoctrine()->getRepository(Organisation::class)->find($request->get('id'));
        if ($organisation != $this->getUser()->getOrganisation()) {
            throw new \Exception('Wrong Organisation');
        }
        $text = $translator->trans('Kinder betreut von der Organisation');
        $schulen = $organisation->getSchule()->toArray();
        $schuljahre = $schulen[0]->getStadt()->getActives()->toArray();


        return $this->render('child/child.html.twig', [

            'organisation' => $organisation,
            'schuljahre' => $schuljahre,
            'text'=>$text
        ]);
    }
    /**
     * @Route("/org_child/show/detail", name="child_detail")
     */
    public function childDetail(Request $request, TranslatorInterface $translator)
    {
        $kind = $this->getDoctrine()->getRepository(Kind::class)->find($request->get('kind_id'));
        $history= $this->getDoctrine()->getRepository(Kind::class)->findBy(array('tracing'=>$kind->getTracing(),'saved'=>true));
         if ($kind->getSchule()->getOrganisation()!= $this->getUser()->getOrganisation()) {
             throw new \Exception('Wrong Organisation');
         }

         return $this->render('child/childDetail.html.twig',array('k'=>$kind,'eltern'=>$kind->getEltern(),'history'=>$history));
    }
    /**
     * @Route("/org_child/print/detail", name="child_detail_print")
     */
    public function printChild(Request $request, TranslatorInterface $translator, PrintService $printService, TCPDFController $TCPDFController)
    {
        $kind = $this->getDoctrine()->getRepository(Kind::class)->find($request->get('kind_id'));

        if ($kind->getSchule()->getOrganisation()!= $this->getUser()->getOrganisation()) {
            throw new \Exception('Wrong Organisation');
        }
        $fileName = $kind->getVorname().'_'.$kind->getNachname();
        return $printService->printChildDetail($kind,$kind->getEltern(),$TCPDFController,$fileName,$kind->getSchule()->getOrganisation(),'D');
    }
    /**
     * @Route("/org_child/search/child/table", name="child_child_Table",methods={"GET","POST"})
     */
    public function buildChildTable(Request $request, TranslatorInterface $translator, PrintService $printService, TCPDFController $TCPDFController, ChildExcelService $childExcelService)
    {
        $organisation = $this->getDoctrine()->getRepository(Organisation::class)->find($request->get('organisation'));
        if ($organisation != $this->getUser()->getOrganisation()) {
            throw new \Exception('Wrong Organisation');
        }
        $repo = $this->getDoctrine()->getRepository(Zeitblock::class);
        $em = $this->getDoctrine()->getManager();

        $qb = $this->getDoctrine()->getRepository(Kind::class)->createQueryBuilder('k')
            ->innerJoin('k.zeitblocks','b');


        //$qb = $repo->createQueryBuilder('b');
        $text = $translator->trans('Kinder betreut von der Organisation %organisation%',array('%organisation%'=>$organisation->getName()));
        $blocks = array();
       if($request->get('schule')){

       $schule = $this->getDoctrine()->getRepository(Schule::class)->find($request->get('schule'));
        $qb->andWhere('b.schule = :schule')
           ->setParameter('schule',$schule);
           $text .= $translator->trans(' an der Schule %schule%',array('%schule%' => $schule->getName()));
       }else{

           foreach ($organisation->getSchule() as $key=>$data){
                   $qb->orWhere('b.schule = :schule'.$key)
                   ->setParameter('schule'.$key,$data);
           }

       }
        if($request->get('schuljahr')){
                $jahr = $this->getDoctrine()->getRepository(Active::class)->find($request->get('schuljahr'));
            $qb->andWhere('b.active = :jahr')
                ->setParameter('jahr',$jahr);
             $text .= $translator->trans(' im Schuljahr schuljahr',array('schuljahr' => $jahr->getVon()->format('d.m.Y').'-'.$jahr->getBis()->format('d.m.Y')));
        }
        if($request->get('wochentag') != null){
            $qb->andWhere('b.wochentag = :wochentag')
                ->setParameter('wochentag',$request->get('wochentag'));
             $text .= $translator->trans(' am Wochentag %wochentag%',array('%wochentag%'=>$this->wochentag[$request->get('wochentag')]));
        }
        if($request->get('block')){
            $qb->andWhere('b.id = :block')
                ->setParameter('block',$request->get('block'));
            $text .= $translator->trans(' im ausgewählten Zeitblock');
        }
        if($request->get('klasse')){
            $qb->andWhere('k.klasse = :klasse')
                ->setParameter('klasse',$request->get('klasse'));
            $text .= $translator->trans(' in der Klasse: %klasse%',array('%klasse%'=>$request->get('klasse')));
        }
        $qb->andWhere('k.fin = 1');
        $query = $qb->getQuery();
        $blocks = $result = $query->getResult();
        $kinderU = $blocks;


        if($request->get('print')){
            //todo test im Filename raus
            $fileName = (new \DateTime())->format('d.m.Y_H.i');
            return $printService->printChildList($kinderU,$organisation,$text,$fileName,$TCPDFController,'D');

        }elseif ($request->get('spread')){
            return $this->file($childExcelService->generateExcel($kinderU), 'Kinder.xlsx', ResponseHeaderBag::DISPOSITION_INLINE);
        } else{
            return $this->render('child/childTable.html.twig', [
                'kinder' => $kinderU,
                'text'=>$text
            ]);
        }


    }
}
