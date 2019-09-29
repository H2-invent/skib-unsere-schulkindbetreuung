<?php

namespace App\Controller;

use App\Entity\Active;
use App\Entity\Organisation;
use App\Entity\Schule;
use App\Entity\Zeitblock;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
class BlockController extends AbstractController
{
    /**
     * @Route("/org_block/schule/show", name="block_schulen_schow",methods={"GET"})
     */
    public function showSchulen(Request $request)
    {
        $organisation = $this->getDoctrine()->getRepository(Organisation::class)->find($request->get('id'));
        $schule = $this->getDoctrine()->getRepository(Schule::class)->findBy(array('organisation'=>$organisation));
        if($organisation != $this->getUser()->getOrganisation()){
            throw new \Exception('Wrong Organisation');
        }
        return $this->render('block/schulen.html.twig',array('schule'=>$schule));
    }
    /**
     * @Route("/org_block/schule/block/show", name="block_schule_schow",methods={"GET"})
     */
    public function showBlocks(Request $request)
    {
        $schule = $this->getDoctrine()->getRepository(Schule::class)->find($request->get('id'));
        if($schule->getOrganisation() != $this->getUser()->getOrganisation()){
            throw new \Exception('Wrong Organisation');
        }
        $activity = $this->getDoctrine()->getRepository(Active::class)->findBy(array('stadt'=>$schule->getStadt()),array('bis'=>'desc'));
        $blocks = $this->getDoctrine()->getRepository(Zeitblock::class)->findAll();
        dump($activity);
        return $this->render('block/blocks.html.twig',array('schuljahre'=>$activity,'schule'=>$schule,'blocks'=>$blocks));
    }
}
