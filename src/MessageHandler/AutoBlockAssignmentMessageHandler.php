<?php

namespace App\MessageHandler;

use App\Message\AutoBlockAssignmentMessage;
use App\Repository\AutoBlockAssignmentRepository;
use App\Repository\OrganisationRepository;
use App\Service\AutoBlockAssignmentService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class AutoBlockAssignmentMessageHandler
{
    public function __construct(
        private OrganisationRepository $organisationRepository,
        private AutoBlockAssignmentService $assignmentService,
    )
    {
    }

    public function __invoke(AutoBlockAssignmentMessage $message)
    {
        $organisation = $this->organisationRepository->find($message->getIdOrganisation());
        $this->assignmentService->createDraft($organisation);
    }
}
