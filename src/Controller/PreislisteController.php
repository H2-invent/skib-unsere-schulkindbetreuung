<?php

namespace App\Controller;

use App\Entity\Schule;
use App\Entity\Stadt;
use App\Entity\Zeitblock;
use App\Service\ErrorService;
use App\Service\PreisListeService;
use App\Service\SchuljahrService;
use App\Service\SchulkindBetreuungAdresseService;
use App\Service\StamdatenFromCookie;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

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
