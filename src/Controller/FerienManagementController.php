<?php

namespace App\Controller;

use App\Entity\Ferienblock;
use App\Entity\Organisation;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class FerienManagementController extends AbstractController
{
    /**
     * @Route("/org_ferien/edit/show", name="ferien_management",methods={"GET"})
     */
    public function index(Request $request)
    {

        $organisation = $this->getDoctrine()->getRepository(Organisation::class)->find($request->get('org_id'));
        if ($organisation != $this->getUser()->getOrganisation()) {
            throw new \Exception('Wrong Organisation');
        }
        $blocks = $this->getDoctrine()->getRepository(Ferienblock::class)->findBy(array('organisation'=>$organisation),array('startDate'=>'asc'));
       dump($blocks);
        return $this->render('ferien_management/index.html.twig',array('blocks'=>$blocks));
    }
}
