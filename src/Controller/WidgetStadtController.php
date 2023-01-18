<?php

namespace App\Controller;

use App\Entity\Active;
use App\Entity\Kind;
use App\Entity\Schule;
use App\Entity\Stadt;
use App\Entity\Zeitblock;
use App\Service\ChildInBlockService;
use App\Service\ChildSearchService;
use App\Service\WidgetService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class WidgetStadtController extends AbstractController
{
    /**
     * @Route("/city_dashboard/show/widget/kidsinSchule", name="widget_kids_schule_stadt")
     */
    public function childsInSchule(Request $request, TranslatorInterface $translator, ChildSearchService $childSearchService)
    {
        $stadt = $this->getDoctrine()->getRepository(Stadt::class)->find($request->get('stadt_id'));
        if ($stadt != $this->getUser()->getStadt()) {
            throw new \Exception('Wrong City');
        }

        $stadt = $this->getUser()->getStadt();
        $active = $this->getDoctrine()->getRepository(Active::class)->findActiveSchuljahrFromCity($stadt);
        $schule = $this->getDoctrine()->getRepository(Schule::class)->findOneBy(array('stadt' => $stadt, 'deleted' => false, 'id' => $request->get('schule_id')));

        $kinder = $childSearchService->searchChild(array('schule' => $schule->getId(), 'schuljahr' => $active->getId()), $schule->getOrganisation(), false, $this->getUser(), new \DateTime());
        return new JsonResponse(array('title' => $schule->getName(), 'small' => $translator->trans('Kinder angemeldet'), 'anzahl' => sizeof($kinder), 'symbol' => 'school'));

    }

    /**
     * @Route("/city_dashboard/show/widget/kidsSchuljahr", name="widget_kids_schuljahr_stadt")
     */
    public function schuljahr(Request $request, TranslatorInterface $translator, ChildSearchService $childSearchService)
    {
        $stadt = $this->getDoctrine()->getRepository(Stadt::class)->find($request->get('stadt_id'));
        if ($stadt != $this->getUser()->getStadt()) {
            throw new \Exception('Wrong City');
        }


        $active = $this->getDoctrine()->getRepository(Active::class)->findActiveSchuljahrFromCity($stadt);


        $kinder = $childSearchService->searchChild(array('schuljahr' => $active->getId()), null, false, $this->getUser(), new \DateTime(), null, $stadt);
        return new JsonResponse(array('title' => $translator->trans('Kinder dieses Schuljahr'), 'small' => '', 'anzahl' => sizeof($kinder), 'symbol' => 'people'));
    }

    private function cmp($a, $b)
    {
        if ($a['active']->getVon() == $b['active']->getVon()) {
            return 0;
        }
        return ($a['active']->getVon() < $b['active']->getVon()) ? -1 : 1;
    }

    /**
     * @Route("/city_dashboard/show/widget/kidsOverYears", name="widget_stadt_over_years")
     */
    public function overYears(Request $request, TranslatorInterface $translator, WidgetService $widgetService)
    {
        $stadt = $this->getDoctrine()->getRepository(Stadt::class)->find($request->get('stadt_id'));
        if ($stadt != $this->getUser()->getStadt()) {
            throw new \Exception('Wrong City');
        }
        $active = $this->getDoctrine()->getRepository(Active::class)->findBy(array('stadt' => $stadt));
        $kinder = array();
        foreach ($active as $data) {
            $formatDate = $data->getVon()->format('d.m.Y');


                $kinder[$formatDate] = $widgetService->calcChildsFromSchuljahrAndCity($data,$data->getBis());

        }


        return $this->render('widget_stadt/chartKids.twig', array('kinder' => $kinder));

    }

    /**
     * @Route("/city_dashboard/show/widget/kidsinblocks", name="widget_stadt_kids_in_blocks")
     */
    public function kidsinBlocks(Request $request, TranslatorInterface $translator)
    {
        $stadt = $this->getDoctrine()->getRepository(Stadt::class)->find($request->get('stadt_id'));
        if ($stadt != $this->getUser()->getStadt()) {
            throw new \Exception('Wrong City');
        }

        $result = array();
        $active = $this->getDoctrine()->getRepository(Active::class)->findActiveSchuljahrFromCity($stadt);
        foreach ($stadt->getOrganisations() as $data) {

            $zb = array();
            foreach ($data->getSchule() as $data2) {
                $blocks = $this->getDoctrine()->getRepository(Zeitblock::class)->findBy(array('deleted' => false, 'schule' => $data2, 'active' => $active));
                $b = array();
                foreach ($blocks as $data3) {
                    $b[$data3->getWochentagString()][] = $data3;
                }
                $zb[] = array('schule' => $data2, 'blocks' => $b);

            }
            $result[] = array('org' => $data, 'schule' => $zb);
        }


        return $this->render('widget_stadt/orgs.html.twig', array('stadt' => $stadt, 'result' => $result));
    }

}
