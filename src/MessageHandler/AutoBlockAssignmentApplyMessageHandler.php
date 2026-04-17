<?php

namespace App\MessageHandler;

use App\Message\AutoBlockAssignmentApplyMessage;
use App\Repository\OrganisationRepository;
use App\Service\AutoBlockAssignmentService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class AutoBlockAssignmentApplyMessageHandler
{
    public function __construct(
        private OrganisationRepository $organisationRepository,
        private AutoBlockAssignmentService $assignmentService,
    ) {
    }

    public function __invoke(AutoBlockAssignmentApplyMessage $message): void
    {
        $organisation = $this->organisationRepository->find($message->getIdOrganisation());
        $this->assignmentService->acceptDraft($organisation);
    }
}
