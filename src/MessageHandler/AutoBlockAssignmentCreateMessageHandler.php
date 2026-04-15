<?php

namespace App\MessageHandler;

use App\Message\AutoBlockAssignmentCreateMessage;
use App\Repository\ActiveRepository;
use App\Repository\OrganisationRepository;
use App\Service\AutoBlockAssignmentService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class AutoBlockAssignmentCreateMessageHandler
{
    public function __construct(
        private OrganisationRepository $organisationRepository,
        private ActiveRepository $activeRepository,
        private AutoBlockAssignmentService $assignmentService,
    ) {
    }

    public function __invoke(AutoBlockAssignmentCreateMessage $message): void
    {
        $organisation = $this->organisationRepository->find($message->getIdOrganisation());
        $schuljahr = $this->activeRepository->find($message->getIdSchuljahr());
        $this->assignmentService->createDraft($organisation, $schuljahr);
    }
}
