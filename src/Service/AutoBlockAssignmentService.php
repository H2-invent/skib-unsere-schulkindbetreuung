<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\Organisation;
use App\Message\AutoBlockAssignmentApplyMessage;
use App\Message\AutoBlockAssignmentCreateMessage;
use App\Repository\AutoBlockAssignmentRepository;
use App\Service\AutoBlockAssignment\DraftApplier;
use App\Service\AutoBlockAssignment\DraftCreator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class AutoBlockAssignmentService
{
    public function __construct(
        private DraftCreator $draftCreator,
        private DraftApplier $draftApplier,
        private MessageBusInterface $messageBus,
        private AutoBlockAssignmentRepository $autoBlockAssignmentRepository,
        private EntityManagerInterface $entityManager,
    )
    {
    }

    public function createDraftAsync(Organisation $organisation): void
    {
        $this->messageBus->dispatch(
            new AutoBlockAssignmentCreateMessage($organisation->getId())
        );
    }

    public function createDraft(Organisation $organisation): void
    {
        $autoBlockAssignment = $this->autoBlockAssignmentRepository->findOneBy(['organisation' => $organisation]);
        if ($autoBlockAssignment !== null) {
            $this->entityManager->remove($autoBlockAssignment);
            $this->entityManager->flush();
        }

        $this->entityManager->wrapInTransaction(
            fn() => $this->draftCreator->create($organisation)
        );
    }

    public function acceptDraftAsync(Organisation $organisation): void
    {
        $this->messageBus->dispatch(
            new AutoBlockAssignmentApplyMessage($organisation->getId())
        );
    }

    public function acceptDraft(Organisation $organisation): void
    {
        $autoBlockAssignment = $organisation->getAutoBlockAssignment();
        if ($autoBlockAssignment === null) {
            return;
        }

        $this->entityManager->wrapInTransaction(
            fn() => $this->draftApplier->apply($autoBlockAssignment)
        );
    }

    public function rejectDraft(Organisation $organisation): void
    {
        $autoBlockAssignment = $organisation->getAutoBlockAssignment();
        if ($autoBlockAssignment === null) {
            return;
        }
        $this->entityManager->remove($autoBlockAssignment);
        $this->entityManager->flush();
    }
}
