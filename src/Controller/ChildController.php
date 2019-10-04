<?php

namespace App\Controller;

use App\Entity\Organisation;
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
        dump($schuljahre);
        $blocks = array();
        foreach ($schulen as $data){
           $blocks =  array_merge($blocks, $data->getZeitblocks()->toArray());
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
        ]);
    }
}
