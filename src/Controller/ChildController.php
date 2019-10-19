<?php

namespace App\Controller;

use App\Entity\Active;
use App\Entity\Kind;
use App\Entity\Organisation;
use App\Entity\Zeitblock;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class ChildController extends AbstractController
{
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
        $blocks = array();
        $search = array();
        if($request->get('schuljahr')){
            $jahr = $this->getDoctrine()->getRepository(Active::class)->find($request->get('schuljahr'));
            $search['active'] = $jahr;
            $text = $translator->trans('Kinder betreut von der Organisation organisation im Schuljahr schuljahr',array('organisation'=>$organisation->getName(),
                'schuljahr' => $jahr->getVon()->format('d.m.Y').'-'.$jahr->getBis()->format('d.m.Y')));
        }
        if($request->get('block')){
            $block = $this->getDoctrine()->getRepository(Zeitblock::class)->find($request->get('block'));

            if($block->getSchule()->getOrganisation() == $this->getUser()->getOrganisation()){
               $blocks[] = $block;
                $text = $translator->trans('Kinder im Block block der Schule schule',array('block'=>$block->getVon()->format('H:i').'-'.$block->getBis()->format(
                        'H:i'),
                    'schule'=>$block->getSchule()->getName()));

            }
        }else{
            foreach ($schulen as $data){
                $search['schule'] = $data;
                $blocks =  array_merge($blocks, $this->getDoctrine()->getRepository(Zeitblock::class)->findBy($search));
            }
        }

        $kinder = array();
        foreach ($blocks as $data){
            $kinder =  array_merge($kinder, $data->getKind()->toArray());
        }
        $kinderU = array();
        foreach ($kinder as $data){
            if ($data->getFin() == true){
                $kinderU[$data->getId()] = $data;
            }
        }

        return $this->render('child/child.html.twig', [
            'kinder' => $kinderU,
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

         if ($kind->getSchule()->getOrganisation()!= $this->getUser()->getOrganisation()) {
             throw new \Exception('Wrong Organisation');
         }
         return $this->render('child/childDetail.html.twig',array('k'=>$kind,'eltern'=>$kind->getEltern()));
    }
}
