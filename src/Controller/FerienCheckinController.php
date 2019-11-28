<?php

namespace App\Controller;

use App\Entity\KindFerienblock;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class FerienCheckinController extends AbstractController
{
    /**
     * @Route("/org_ferien/checkin/{checkinID}", name="ferien_checkin")
     * @ParamConverter("KindFerienblock", options={"mapping"={"checkinID"="checkinID"}})
     */
    public function index(KindFerienblock $kindFerienblock)
    {
        $today = new \DateTime('today');
        $checkinID = $kindFerienblock->getCheckinID();
        $startdateFerienblock = $kindFerienblock->getFerienblock()->getStartDate();
        $enddateFerienblock = $kindFerienblock->getFerienblock()->getEndDate();
        $error = [];
        if ($today >= $startdateFerienblock && $today < $enddateFerienblock || $today === $startdateFerienblock){
            $error == true;
    }
        return $this->render('ferien_checkin/index.html.twig', [
            'controller_name' => 'FerienCheckinController',
            'error'=>$error,
            'block'=>$kindFerienblock,
            'kind'=>$kindFerienblock->getKind(),
        ]);
    }
}
