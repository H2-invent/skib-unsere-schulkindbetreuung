<?php

namespace App\Controller;

use App\Entity\Active;
use App\Entity\Organisation;
use App\Entity\Zeitblock;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ChildController extends AbstractController
{
    /**
     * @Route("/org_child/show", name="child_show")
     */
    public function showAction(Request $request)
    {
        $organisation = $this->getDoctrine()->getRepository(Organisation::class)->find($request->get('id'));
        if ($organisation != $this->getUser()->getOrganisation()) {
            throw new \Exception('Wrong Organisation');
        }
        $schulen = $organisation->getSchule()->toArray();
        $schuljahre = $schulen[0]->getStadt()->getActives()->toArray();
        $blocks = array();
        $search = array();
        if($request->get('schuljahr')){
            $search['active'] = $this->getDoctrine()->getRepository(Active::class)->find($request->get('schuljahr'));

        }
        foreach ($schulen as $data){
            $search['schule'] = $data;
           $blocks =  array_merge($blocks, $this->getDoctrine()->getRepository(Zeitblock::class)->findBy($search));
        }
        $kinder = array();
        foreach ($blocks as $data){
            $kinder =  array_merge($kinder, $data->getKind()->toArray());
        }
        $kinderU = array();
        foreach ($kinder as $data){
            $kinderU[$data->getId()] = $data;
        }


        return $this->render('child/child.html.twig', [
            'kinder' => $kinderU,
            'organisation' => $organisation,
            'schuljahre' => $schuljahre,
        ]);
    }
}
