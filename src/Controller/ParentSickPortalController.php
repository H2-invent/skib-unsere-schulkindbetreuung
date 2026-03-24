<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\ChildSickReport;
use App\Entity\ParentSickPortalAccess;
use App\Entity\User;
use App\Form\Type\ParentSickAccessRequestType;
use App\Repository\ChildSickReportRepository;
use App\Repository\KindRepository;
use App\Repository\ParentSickPortalAccessRepository;
use App\Service\ParentSickPortalService;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ParentSickPortalController extends AbstractController
{
    private const SESSION_KEY_STADT_SLUG = 'parent_sick_stadt_slug';

    public function __construct(
        private ParentSickPortalService $portalService,
        private ParentSickPortalAccessRepository $accessRepository,
        private KindRepository $kindRepository,
        private ChildSickReportRepository $sickReportRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('/eltern/krankmeldung', name: 'parent_sick_request', methods: ['GET', 'POST'])]
    public function requestLink(Request $request): Response
    {
        $stadtSlug = $request->query->get('stadt');
        $stadt = $this->entityManager->getRepository(\App\Entity\Stadt::class)->findOneBy(['slug' => $stadtSlug]);

        if (!$stadt instanceof \App\Entity\Stadt) {
            throw $this->createNotFoundException('Stadt nicht gefunden.');
        }
        $request->getSession()->set(self::SESSION_KEY_STADT_SLUG, $stadt->getSlug());

        $form = $this->createForm(ParentSickAccessRequestType::class, new ParentSickPortalAccess(), [
            'schuljahre' => $stadt->getActives()->toArray(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var ParentSickPortalAccess $access */
            $access = $form->getData();
            $this->portalService->createAndSendAccessLink($access, $stadt);
            $this->addFlash('success', 'Der Link wurde per E-Mail verschickt.');

            return $this->redirectToRoute('parent_sick_request', ['stadt' => $stadt->getSlug()]);
        }

        return $this->render('parent_sick/request.html.twig', [
            'form' => $form->createView(),
            'stadt' => $stadt,
        ]);
    }

    #[Route('/eltern/krankmeldung/start/{token}', name: 'parent_sick_start', methods: ['GET'])]
    #[Entity('access', expr: 'repository.findByStringToken(token)')]
    public function start(Request $request, ParentSickPortalAccess $access): Response
    {
        if (!$this->portalService->isLinkValid($access, $request)) {
            throw $this->createAccessDeniedException('Der Link ist ungültig oder abgelaufen.');
        }

        $session = $request->getSession();
        $session->set(ParentSickPortalService::SESSION_KEY_ACCESS, $access->getId());

        $access->markUsed();
        $this->entityManager->persist($access);
        $this->entityManager->flush();

        return $this->redirectToRoute('parent_sick_dashboard');
    }

    #[Route('/eltern/krankmeldung/dashboard', name: 'parent_sick_dashboard', methods: ['GET'])]
    public function dashboard(Request $request): Response
    {
        $access = $this->getAccessFromSession($request);
        if (!$access instanceof ParentSickPortalAccess) {
            $stadtSlug = $request->getSession()->get(self::SESSION_KEY_STADT_SLUG);

            return $this->redirectToRoute('parent_sick_request', ['stadt' => $stadtSlug]);
        }

        $childHistory = $this->kindRepository->findChildHistoryForParentAndSchoolyear($access->getEmail(), $access->getSchuljahr());
        $registrations = [];
        foreach ($childHistory as $historyEntry) {
            $parent = $historyEntry->getEltern();
            $registrationKey = $parent?->getTracing() ?? ('registration_' . $historyEntry->getId());

            if (!isset($registrations[$registrationKey])) {
                $registrations[$registrationKey] = [
                    'parent' => $parent,
                    'children' => [],
                ];
            }

            $registrations[$registrationKey]['children'][$historyEntry->getTracing()][] = $historyEntry;
        }

        $todayReports = [];
        foreach ($registrations as $registrationData) {
            foreach ($registrationData['children'] as $entries) {
                $latest = end($entries);
                if ($latest) {
                    $todayReports[$latest->getId()] = $this->sickReportRepository->findLatestForChild($latest);
                }
            }
        }

        return $this->render('parent_sick/dashboard.html.twig', [
            'access' => $access,
            'registrations' => $registrations,
            'todayReports' => $todayReports,
        ]);
    }

    #[Route('/eltern/krankmeldung/{kind}/save', name: 'parent_sick_save', methods: ['POST'])]
    public function saveReport(Request $request, int $kind): Response
    {
        $access = $this->getAccessFromSession($request);
        if (!$access instanceof ParentSickPortalAccess) {
            $stadtSlug = $request->getSession()->get(self::SESSION_KEY_STADT_SLUG);

            return $this->redirectToRoute('parent_sick_request', ['stadt' => $stadtSlug]);
        }

        $childHistory = $this->kindRepository->findChildHistoryForParentAndSchoolyear($access->getEmail(), $access->getSchuljahr());
        $allowedChildIds = array_map(static fn($child) => $child->getId(), $childHistory);
        if (!in_array($kind, $allowedChildIds, true)) {
            throw $this->createAccessDeniedException('Kind nicht im Zugriff enthalten.');
        }

        $kindEntity = $this->kindRepository->find($kind);
        if (!$kindEntity) {
            throw $this->createNotFoundException('Kind nicht gefunden.');
        }

        $von = new \DateTime((string)$request->request->get('von', 'today'));
        $bis = new \DateTime((string)$request->request->get('bis', $von->format('Y-m-d')));
        if ($bis < $von) {
            $bis = clone $von;
        }

        $report = (new ChildSickReport())
            ->setAccess($access)
            ->setKind($kindEntity)
            ->setVon($von)
            ->setBis($bis)
            ->setBemerkung($request->request->get('bemerkung'));

        $this->entityManager->persist($report);
        $this->entityManager->flush();

        $this->addFlash('success', 'Krankmeldung wurde gespeichert.');

        return $this->redirectToRoute('parent_sick_dashboard');
    }

    #[Route('/eltern/krankmeldung/logout', name: 'parent_sick_logout', methods: ['GET'])]
    public function logout(Request $request): Response
    {
        $request->getSession()->remove(ParentSickPortalService::SESSION_KEY_ACCESS);
        $this->addFlash('success', 'Zugriff wurde beendet.');
        $stadtSlug = $request->getSession()->get(self::SESSION_KEY_STADT_SLUG);

        return $this->redirectToRoute('parent_sick_request', ['stadt' => $stadtSlug]);
    }

    #[Route('/org_child/krankmeldungen/heute', name: 'org_child_sick_today', methods: ['GET'])]
    public function todayForOrg(): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $organisation = $user->getOrganisation();

        $reports = $this->sickReportRepository->findForTodayByOrganisation($organisation);

        return $this->render('parent_sick/today_for_org.html.twig', [
            'reports' => $reports,
        ]);
    }

    private function getAccessFromSession(Request $request): ?ParentSickPortalAccess
    {
        $id = $request->getSession()->get(ParentSickPortalService::SESSION_KEY_ACCESS);
        if (!$id) {
            return null;
        }

        return $this->accessRepository->find((int)$id);
    }
}
