<?php

namespace App\Controller;

use App\Entity\Schule;
use App\Entity\Stadt;
use App\Entity\Zeitblock;
use App\Service\ErrorService;
use App\Service\SchuljahrService;
use App\Service\SchulkindBetreuungAdresseService;
use App\Service\StamdatenFromCookie;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
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
    public function adresseAction(Stadt $stadt, Schule $schule, Request $request, SchuljahrService $schuljahrService)
    {
        $schuljahr = $schuljahrService->getSchuljahr($stadt);
        $schulen = $stadt->getSchules();
        $gehalt = $stadt->getGehaltsklassen();
        $art = [
            'Ganztag' => 1,
            'Halbtag' => 2,
        ];

        $gehaltIst = $request->get('gehalt', sizeof($gehalt) - 1);
        dump($gehaltIst);
        $artIst = $request->get('art', 1);
        $req = array(
            'deleted' => false,
            'active' => $schuljahr,
            'schule' => $schule,
        );

        $req['ganztag'] = $artIst;
        $block = array();
        $block = $this->getDoctrine()->getRepository(Zeitblock::class)->findBy($req, array('von' => 'asc'));

        $renderBlocks = array();
        foreach ($block as $data) {
            $renderBlocks[$data->getWochentag()][] = $data;
        }

        return $this->render('preisliste/index.html.twig', [
            'schulen' => $schulen,
            'gehalt' => $gehalt,
            'art' => array_flip($art),
            'schule' => $schule,
            'gehaltIst' => $gehaltIst,
            'blocks' => $renderBlocks,
            'artIst' => $artIst
        ]);
    }
}
