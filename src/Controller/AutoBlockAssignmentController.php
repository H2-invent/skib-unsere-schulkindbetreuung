<?php

namespace App\Controller;

use App\Entity\Organisation;
use App\Repository\ActiveRepository;
use App\Repository\AutoBlockAssignmentChildRepository;
use App\Repository\OrganisationRepository;
use App\Repository\ZeitblockRepository;
use App\Service\AutoBlockAssignmentService;
use Exception;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AutoBlockAssignmentController extends AbstractController
{
    public function __construct(
        private OrganisationRepository $organisationRepository,
        private AutoBlockAssignmentChildRepository $autoChildRepository,
        private ZeitblockRepository $zeitblockRepository,
        private AutoBlockAssignmentService $autoBlockAssignmentService,
        private ActiveRepository $activeRepository,
    )
    {
    }

    /**
     * @throws Exception
     */
    #[Route(path: '/org_child/auto_assign', name: 'org_child_auto_assign')]
    public function index(Request $request): Response
    {
        $idOrganisation = $request->get('id');
        $organisation = $this->organisationRepository->find($idOrganisation);
        $assignmentStarted = $request->get('assignment_started', false);

        $schuljahre = $this->activeRepository->findBy(['stadt' => $organisation->getStadt()], ['bis' => 'desc']);

        $this->assertUserAndOrgaAllowed($organisation);

        if ($organisation->getAutoBlockAssignment()) {
            return $this->redirectToRoute('org_child_auto_assign_confirm', ['id' => $idOrganisation]);
        }

        $schulen = $organisation->getSchule();
        $schulDaten = [];
        foreach ($schulen as $schule) {
            $countSchule = count($this->zeitblockRepository->findBeworbenBlocksBySchule($schule));
            $schulDaten[] = [
                'schule' => $schule,
                'count' => $countSchule,
            ];
        }


        return $this->render('auto_block_assignment/index.html.twig', [
            'schulData' => $schulDaten,
            'schuljahre' => $schuljahre,
            'assignment_started' => $assignmentStarted,
        ]);
    }

    #[Route(path: '/org_child/auto_assign/start', name: 'org_child_auto_assign_start')]
    public function start(Request $request): Response
    {
        $idOrganisation = $request->get('id');
        $organisation = $this->organisationRepository->find($idOrganisation);

        $idSchuljahr = $request->get('schuljahr');
        $schuljahr = $this->activeRepository->find($idSchuljahr);

        $this->assertUserAndOrgaAllowed($organisation);

        $this->autoBlockAssignmentService->createDraftAsync($organisation, $schuljahr);

        return $this->redirectToRoute('org_child_auto_assign', [
            'id' => $idOrganisation,
            'assignment_started' => true,
        ]);
    }

    #[Route(path: '/org_child/auto_assign/confirm', name: 'org_child_auto_assign_confirm')]
    public function confirm(Request $request): Response
    {
        $idOrganisation = $request->get('id');
        $assignmentStarted = $request->get('assignment_started', false);
        $organisation = $this->organisationRepository->find($idOrganisation);
        $this->assertUserAndOrgaAllowed($organisation);

        if ($organisation->getAutoBlockAssignment() === null) {
            return $this->redirectToRoute('org_child_auto_assign', ['id' => $idOrganisation]);
        }

        $autoBlockChildren = $this->autoChildRepository->findByOrganisationAddZeitblocksCounts($organisation);

        return $this->render('auto_block_assignment/confirm.html.twig', [
            'autoBlockChildren' => $autoBlockChildren,
            'assignment_started' => $assignmentStarted,
        ]);
    }

    #[Route(path: '/org_child/auto_assign/confirm/child/{id}', name: 'org_child_auto_assign_confirm_child')]
    public function confirmChildRow(Request $request): JsonResponse
    {
        $idChild = $request->get('id');
        $child = $this->autoChildRepository->find($idChild);
        if ($child === null) {
            return $this->json([], Response::HTTP_NOT_FOUND);
        }
        $autoZeitblocks = $child?->getZeitblocks();

        return $this->json($autoZeitblocks, context: ['groups' => 'confirm_child']);
    }

    #[Route(path: '/org_child/auto_assign/accept', name: 'org_child_auto_assign_accept')]
    public function accept(Request $request): Response
    {
        $idOrganisation = $request->get('id');
        $organisation = $this->organisationRepository->find($idOrganisation);
        $this->assertUserAndOrgaAllowed($organisation);

        $this->autoBlockAssignmentService->acceptDraftAsync($organisation);

        return $this->redirectToRoute('org_child_auto_assign_confirm', [
            'id' => $idOrganisation,
            'assignment_started' => true
        ]);
    }

    #[Route(path: '/org_child/auto_assign/reject', name: 'org_child_auto_assign_reject')]
    public function reject(Request $request): Response
    {
        $idOrganisation = $request->get('id');
        $organisation = $this->organisationRepository->find($idOrganisation);
        $this->assertUserAndOrgaAllowed($organisation);

        $this->autoBlockAssignmentService->rejectDraft($organisation);

        return $this->redirectToRoute('org_child_auto_assign', ['id' => $idOrganisation]);
    }

    #[Route(path: '/org_child/auto_assign/status', name: 'org_child_auto_assign_status')]
    public function status(Request $request): JsonResponse
    {
        $idOrganisation = $request->get('id');
        $organisation = $this->organisationRepository->find($idOrganisation);
        $this->assertUserAndOrgaAllowed($organisation);

        $isDone = $organisation?->getAutoBlockAssignment() !== null;

        return $this->json(['done' => $isDone]);
    }

    #[Route(path: '/org_child/auto_assign/status-apply', name: 'org_child_auto_assign_status_apply')]
    public function statusApply(Request $request): JsonResponse
    {
        $idOrganisation = $request->get('id');
        $organisation = $this->organisationRepository->find($idOrganisation);
        $this->assertUserAndOrgaAllowed($organisation);

        $isDone = $organisation?->getAutoBlockAssignment() === null;

        return $this->json(['done' => $isDone]);
    }

    /**
     * @param Organisation|null $organisation
     * @throws Exception
     */
    private function assertUserAndOrgaAllowed(?Organisation $organisation): void
    {
        if ($organisation === null || $organisation->getStadt() !== $this->getUser()?->getStadt()) {
            throw new RuntimeException('Wrong City');
        }
    }
}
