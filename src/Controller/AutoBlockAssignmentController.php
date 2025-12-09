<?php

namespace App\Controller;

use App\Repository\KindRepository;
use App\Repository\OrganisationRepository;
use App\Repository\ZeitblockRepository;
use App\Service\AutoBlockAssignmentService;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AutoBlockAssignmentController extends AbstractController
{
    public function __construct(
        private OrganisationRepository $organisationRepository,
        private KindRepository $kindRepository,
        private ZeitblockRepository $zeitblockRepository,
        private AutoBlockAssignmentService $autoBlockAssignmentService,
    )
    {}

    /**
     * @Route("/org_child/auto_assign", name="org_child_auto_assign")
     * @throws Exception
     */
    public function index(Request $request): Response
    {
        /**
         * TODO
         * Sucht nach AutoBlockAssignment für Organisation (1 to 1 Relation) und zeigt diese an (org_child_auto_assign_confirm)
         * Reject löscht diese
         */

        $idOrganisation = $request->get('id');
        $organisation = $this->organisationRepository->find($idOrganisation);
        $assignmentStarted = $request->get('assignment_started', false);

        if ($organisation === null || $organisation->getStadt() !== $this->getUser()->getStadt()) {
            throw new Exception('Wrong City');
        }
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
            'assignment_started' => $assignmentStarted,
        ]);
    }

    /**
     * @Route("/org_child/auto_assign/start", name="org_child_auto_assign_start")
     */
    public function start(Request $request): Response
    {
        $idOrganisation = $request->get('id');
        $organisation = $this->organisationRepository->find($idOrganisation);
        $this->autoBlockAssignmentService->createDraftAsync($organisation);

        return $this->redirectToRoute('org_child_auto_assign', [
            'id' => $idOrganisation,
            'assignment_started' => true,
        ]);
    }

    /**
     * @Route("/org_child/auto_assign/confirm", name="org_child_auto_assign_confirm")
     */
    public function confirm(): Response
    {
        $kinder = $this->kindRepository->findBy([], [], 50);

        return $this->render('auto_block_assignment/confirm.html.twig', [
            'kinder' => $kinder,
        ]);
    }

    /**
     * @Route("/org_child/auto_assign/accept", name="org_child_auto_assign_accept")
     */
    public function accept(Request $request): Response
    {
        $idOrganisation = $request->get('id');
        $organisation = $this->organisationRepository->find($idOrganisation);
        $this->autoBlockAssignmentService->acceptDraft($organisation);

        return $this->redirectToRoute('org_child_auto_assign', ['id' => $idOrganisation]);
    }

    /**
     * @Route("/org_child/auto_assign/reject", name="org_child_auto_assign_reject")
     */
    public function reject(Request $request): Response
    {
        $idOrganisation = $request->get('id');
        $organisation = $this->organisationRepository->find($idOrganisation);
        $this->autoBlockAssignmentService->rejectDraft($organisation);

        return $this->redirectToRoute('org_child_auto_assign', ['id' => $idOrganisation]);
    }

    /**
     * @Route("/org_child/auto_assign/status", name="org_child_auto_assign_status")
     */
    public function status(Request $request): JsonResponse
    {
        $idOrganisation = $request->get('id');
        $organisation = $this->organisationRepository->find($idOrganisation);
        $isDone = $organisation?->getAutoBlockAssignment() !== null;

        return $this->json(['done' => $isDone]);
    }
}
