<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\AutoBlockAssignment;
use App\Entity\Organisation;
use App\Message\AutoBlockAssignmentMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class AutoBlockAssignmentService
{
    public function __construct(
        private MessageBusInterface $messageBus,
        private EntityManagerInterface $entityManager,
    )
    {
    }

    public function createDraftAsync(Organisation $organisation): void
    {
        $this->messageBus->dispatch(
            new AutoBlockAssignmentMessage($organisation->getId())
        );
    }

    public function createDraft(Organisation $organisation): void
    {
        //TODO check if already a autoblockassignment and use that instead
        $autoBlockAssignment = new AutoBlockAssignment();
        $autoBlockAssignment->setOrganisation($organisation);
        $autoBlockAssignment->setFinished(true); //TODO remove finished

        $this->entityManager->persist($autoBlockAssignment);
        $this->entityManager->flush();
    }

    public function acceptDraft(Organisation $organisation): void
    {
        $autoBlockAssignment = $organisation->getAutoBlockAssignment();
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
