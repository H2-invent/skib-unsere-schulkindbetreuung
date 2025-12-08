<?php

namespace App\Controller;

use App\Repository\KindRepository;
use App\Repository\OrganisationRepository;
use App\Repository\ZeitblockRepository;
use App\Service\AutoBlockAssignmentService;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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

        $organisation = $this->organisationRepository->find($request->get('id'));
        $assignmentStarted = $request->get('assignment_started', false);

        if ($organisation === null || $organisation->getStadt() !== $this->getUser()->getStadt()) {
            throw new Exception('Wrong City');
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
        $idOrganistion = $request->get('id');
        $organisation = $this->organisationRepository->find($idOrganistion);
        $this->autoBlockAssignmentService->startAsync($organisation);

        return $this->redirectToRoute('org_child_auto_assign', [
            'id' => $idOrganistion,
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
        $organisationId = $request->get('id');
        return $this->redirectToRoute('org_child_auto_assign', ['id' => $organisationId]);
    }

    /**
     * @Route("/org_child/auto_assign/reject", name="org_child_auto_assign_reject")
     */
    public function reject(Request $request): Response
    {
        $organisationId = $request->get('id');
        return $this->redirectToRoute('org_child_auto_assign', ['id' => $organisationId]);
    }

}
