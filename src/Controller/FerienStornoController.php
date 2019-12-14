<?php

namespace App\Controller;

use App\Entity\Ferienblock;
use App\Entity\Kind;
use App\Entity\KindFerienblock;
use App\Entity\Stadt;
use App\Entity\Stammdaten;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class FerienStornoController extends AbstractController
{
    /**
     * @Route("/{slug}/ferien/storno", name="ferien_storno")
     */
    public function index($slug, Request $request)
    {
        $stadt = $this->getDoctrine()->getRepository(Stadt::class)->findOneBy(array('slug' => $slug));
        $stammdaten = $this->getDoctrine()->getRepository(Stammdaten::class)->findOneBy(array('uid' => $request->get('parent_id')));
        //$kindFerienblock = $this->getDoctrine()->getRepository(KindFerienblock::class)->findOneBy(array('kind' => $request->get('kind_id')));

        $kind = $stammdaten ->getKinds();

        return $this->render('ferien_storno/index.html.twig', [
            'stadt' => $stadt,
            'kind' => $kind,
            'eltern' => $stammdaten,
        ]);
    }


    /**
     * @Route("/ferien/storno/mark", name="ferien_storno_mark", methods={"PATCH"})
     */
    public function markAction(Request $request)
    {
        $stammdaten = $this->getDoctrine()->getRepository(Stammdaten::class)->findOneBy(array('uid' => $request->get('parent_id')));
        $kindFerienblock = $this->getDoctrine()->getRepository(KindFerienblock::class)->findOneBy(array('id' => $request->get('block_id')));
        $kind = $this->getDoctrine()->getRepository(Kind::class)->findOneBy(array('eltern' => $stammdaten, 'id' => $request->get('kind_id')));
        //$result = $toogleKindFerienblock->toggleKind($kind, $block, $request->get('preis_id'));

        if ($stammdaten->getUid() != $request->get('parent_id')) {
            $result = 0;
            return new JsonResponse($result);
        }

        if ($kindFerienblock->getState() == 20) {
            $result['text'] = 'Ferienprogram bereits storniert';
        }
        $kindFerienblock-> setState(20);
        $result['cardText'] = $this->trans('Storniert');
        $result['text'] = 'Ferienprogram wurde storniert';
        $result['error'] = 0;
        $result['state'] = 20;
        $em = $this->getDoctrine()->getManager();
        $em->flush();

        dump($kindFerienblock);
        return new JsonResponse($result);

    }


}
