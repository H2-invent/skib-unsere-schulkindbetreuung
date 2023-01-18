<?php

namespace App\Controller;

use App\Entity\Active;
use App\Entity\Stadt;
use App\Entity\Zeitblock;
use App\Service\ChildSearchService;
use App\Service\ElternService;
use App\Service\StadtBerichtService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class BerichtController extends AbstractController
{
    /**
     * @Route("/city_report/index", name="stadt_bericht_index")
     */
    public function index(Request $request)
    {
        $stadt = $this->getDoctrine()->getRepository(Stadt::class)->find($request->get('stadt_id'));
        if ($stadt != $this->getUser()->getStadt()) {
            throw new \Exception('Wrong City');
        }

        $qb = $this->getDoctrine()->getRepository(Zeitblock::class)->createQueryBuilder('b');


        foreach ($stadt->getSchules() as $key => $data) {
            $qb->orWhere('b.schule = :schule' . $key)
                ->setParameter('schule' . $key, $data);
        }
        $jahr = null;
        if ($request->get('schuljahr')) {
            $jahr = $this->getDoctrine()->getRepository(Active::class)->find($request->get('schuljahr'));
            $qb->andWhere('b.active = :jahr')
                ->setParameter('jahr', $jahr);
        }

        $query = $qb->getQuery();
        $blocks = $query->getResult();
        $schuljahre = $this->getDoctrine()->getRepository(Active::class)->findBy(array('stadt' => $stadt));
        return $this->render('bericht/index.html.twig', array('blocks' => $blocks, 'schuljahre' => $schuljahre, 'active' => $jahr, 'stadt' => $stadt));
    }

    /**
     * @Route("/city_report/export", name="stadt_bericht_export")
     */
    public function export(Request $request, TranslatorInterface $translator, StadtBerichtService $stadtBerichtService, ChildSearchService $childSearchService, ElternService $elternService)
    {

        $blocks = array();
        $kinder = array();
        $eltern = array();
        $elternT = array();
        $kinderT = array();

        $stadt = $this->getDoctrine()->getRepository(Stadt::class)->find($request->get('stadt_id'));
        if ($stadt != $this->getUser()->getStadt()) {
            throw new \Exception('Wrong City');
        }

        if ($request->get('schuljahr')) {
            $schuljahr = $this->getDoctrine()->getRepository(Active::class)->findBy(array('stadt' => $stadt, 'id' => $request->get('schuljahr')));

        } else {
            $schuljahr = $this->getDoctrine()->getRepository(Active::class)->findBy(array('stadt' => $stadt));

        }

        foreach ($schuljahr as $data) {
            $blocks = array_merge($blocks, $data->getBlocks()->toArray());
            $kinder = $childSearchService->searchChild(array('schuljahr' => $data->getId()), null, false, null, $data->getBis(), null, $stadt);
            foreach ($kinder as $data2) {
                $elternT[] = $elternService->getElternForSpecificTimeAndKind($data2, $data->getBis());
            }
        }


        $eltern = array_unique($elternT);// Return the excel file as an attachment


        return $this->file($stadtBerichtService->generateExcel($blocks, $kinder, $eltern, $stadt), 'Bericht.xlsx', ResponseHeaderBag::DISPOSITION_INLINE);
    }
}
