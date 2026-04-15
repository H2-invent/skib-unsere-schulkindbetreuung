<?php

namespace App\Controller;

use App\Entity\Active;
use App\Entity\Schule;
use App\Entity\Stadt;
use App\Entity\Zeitblock;
use App\Service\ChildSearchService;
use App\Service\WidgetService;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class WidgetStadtController extends AbstractController
{
    public function __construct(
        private ManagerRegistry $managerRegistry,
    ) {
    }

    #[Route(path: '/city_dashboard/show/widget/kidsinSchule', name: 'widget_kids_schule_stadt')]
    public function childsInSchule(Request $request, TranslatorInterface $translator, ChildSearchService $childSearchService)
    {
        $stadt = $this->managerRegistry->getRepository(Stadt::class)->find($request->get('stadt_id'));
        if ($stadt != $this->getUser()->getStadt()) {
            throw new \Exception('Wrong City');
        }

        $stadt = $this->getUser()->getStadt();
        $active = $this->managerRegistry->getRepository(Active::class)->findActiveSchuljahrFromCity($stadt);
        $schule = $this->managerRegistry->getRepository(Schule::class)->findOneBy(['stadt' => $stadt, 'deleted' => false, 'id' => $request->get('schule_id')]);

        $kinder = $childSearchService->searchChild(['schule' => $schule->getId(), 'schuljahr' => $active->getId()], $schule->getOrganisation(), false, $this->getUser(), new \DateTime());

        return new JsonResponse(['title' => $schule->getName(), 'small' => $translator->trans('Kinder angemeldet'), 'anzahl' => sizeof($kinder), 'symbol' => 'school']);
    }

    #[Route(path: '/city_dashboard/show/widget/kidsSchuljahr', name: 'widget_kids_schuljahr_stadt')]
    public function schuljahr(Request $request, TranslatorInterface $translator, ChildSearchService $childSearchService)
    {
        $stadt = $this->managerRegistry->getRepository(Stadt::class)->find($request->get('stadt_id'));
        if ($stadt != $this->getUser()->getStadt()) {
            throw new \Exception('Wrong City');
        }

        $active = $this->managerRegistry->getRepository(Active::class)->findActiveSchuljahrFromCity($stadt);

        $kinder = $childSearchService->searchChild(['schuljahr' => $active->getId()], null, false, $this->getUser(), new \DateTime(), null, $stadt);

        return new JsonResponse(['title' => $translator->trans('Kinder dieses Schuljahr'), 'small' => '', 'anzahl' => sizeof($kinder), 'symbol' => 'people']);
    }

    private function cmp($a, $b)
    {
        return $a['active']->getVon() <=> $b['active']->getVon();
    }

    #[Route(path: '/city_dashboard/show/widget/kidsOverYears', name: 'widget_stadt_over_years')]
    public function overYears(Request $request, TranslatorInterface $translator, WidgetService $widgetService)
    {
        $stadt = $this->managerRegistry->getRepository(Stadt::class)->find($request->get('stadt_id'));
        if ($stadt != $this->getUser()->getStadt()) {
            throw new \Exception('Wrong City');
        }
        $active = $this->managerRegistry->getRepository(Active::class)->findBy(['stadt' => $stadt]);
        $kinder = [];
        foreach ($active as $data) {
            $formatDate = $data->getVon()->format('d.m.Y');

            $kinder[$formatDate] = $widgetService->calcChildsFromSchuljahrAndCity($data, $data->getBis());
        }

        return $this->render('widget_stadt/chartKids.twig', ['kinder' => $kinder]);
    }

    #[Route(path: '/city_dashboard/show/widget/kidsinblocks', name: 'widget_stadt_kids_in_blocks')]
    public function kidsinBlocks(Request $request, TranslatorInterface $translator)
    {
        $stadt = $this->managerRegistry->getRepository(Stadt::class)->find($request->get('stadt_id'));
        if ($stadt != $this->getUser()->getStadt()) {
            throw new \Exception('Wrong City');
        }

        $result = [];
        $active = $this->managerRegistry->getRepository(Active::class)->findActiveSchuljahrFromCity($stadt);
        foreach ($stadt->getOrganisations() as $data) {
            $zb = [];
            foreach ($data->getSchule() as $data2) {
                $blocks = $this->managerRegistry->getRepository(Zeitblock::class)->findBy(['deleted' => false, 'schule' => $data2, 'active' => $active]);
                $b = [];
                foreach ($blocks as $data3) {
                    $b[$data3->getWochentagString()][] = $data3;
                }
                $zb[] = ['schule' => $data2, 'blocks' => $b];
            }
            $result[] = ['org' => $data, 'schule' => $zb];
        }

        return $this->render('widget_stadt/orgs.html.twig', ['stadt' => $stadt, 'result' => $result]);
    }
}
