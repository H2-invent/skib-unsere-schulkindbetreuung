<?php

namespace App\Controller;

use App\Entity\Schule;
use App\Entity\Stadt;
use App\Service\PreisListeService;
use App\Service\SchuljahrService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PreislisteController extends AbstractController
{
    /**
     * @Route("/{slug}/preisliste/{schule}",name="trager_preisliste",methods={"GET"})
     * @ParamConverter("stadt", options={"mapping"={"slug"="slug"}})
     * * @ParamConverter("schule", options={"mapping"={"schule"="id"}})
     */
    public function adresseAction(PreisListeService $preisListeService, Stadt $stadt, Schule $schule, Request $request, SchuljahrService $schuljahrService)
    {
        $gehaltIst = $request->get('gehalt', sizeof($stadt->getGehaltsklassen()) - 1);
        $artIst = $request->get('art', 1);
        return new Response($preisListeService->preisliste($stadt,$schule,$gehaltIst,$artIst));

    }
}
