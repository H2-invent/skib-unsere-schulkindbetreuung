<?php

namespace App\Controller;

use App\Entity\Ferienblock;
use App\Entity\Kind;
use App\Entity\KindFerienblock;
use App\Entity\Stadt;
use App\Entity\Stammdaten;
use App\Service\FerienStornoService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use function Doctrine\ORM\QueryBuilder;

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
    public function markAction(TranslatorInterface $translator, Request $request,FerienStornoService $ferienStornoService)
    {
        $stammdaten = $this->getDoctrine()->getRepository(Stammdaten::class)->findOneBy(array('uid' => $request->get('parent_id')));
        $kindFerienblock = $this->getDoctrine()->getRepository(KindFerienblock::class)->findOneBy(array('id' => $request->get('block_id')));
        return new JsonResponse($ferienStornoService->toggleBlock($kindFerienblock,$stammdaten));
    }

    /**
     * @Route("/{slug}/ferien/storno/abschluss", name="ferien_storno_abschluss", methods={"GET"})
     */
    public function stornoAbschluss($slug,TranslatorInterface $translator, Request $request,FerienStornoService $ferienStornoService)
    {
        $stadt = $this->getDoctrine()->getRepository(Stadt::class)->findOneBy(array('slug' => $slug));

        $stammdaten = $this->getDoctrine()->getRepository(Stammdaten::class)->findOneBy(array('uid' => $request->get('parent_id')));
        $ferienStornoService->stornoAbschluss($stammdaten,$request->getClientIp());
            return 0;
        return $this->redirectToRoute('ferien_abschluss',array('slug'=>$stadt->getSlug()));
    }
}
