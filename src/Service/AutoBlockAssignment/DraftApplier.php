<?php
declare(strict_types=1);

namespace App\Service\AutoBlockAssignment;

use App\Entity\AutoBlockAssignment;
use App\Repository\AutoBlockAssignmentChildZeitblockRepository;
use App\Service\KontingentAcceptService;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;

class DraftApplier
{
    public function __construct(
        private KontingentAcceptService $kontingentAcceptService,
        private AutoBlockAssignmentChildZeitblockRepository $autoZeitblockRepository,
        private EntityManagerInterface $entityManager,
    )
    {
    }

    public function apply(AutoBlockAssignment $autoBlockAssignment): void
    {
        $organisation = $autoBlockAssignment->getOrganisation();
        if ($organisation === null) {
            throw new RuntimeException('Could not find Organisation of AutoBlockAssignment: ' . $autoBlockAssignment->getId());
        }

        $autoZeitblocks = $this->autoZeitblockRepository->findAcceptedJoinChildAndKindByOrganisation($organisation);
        foreach ($autoZeitblocks as $autoZeitblock) {
            $kind = $autoZeitblock->getChild()?->getKind();
            if ($kind === null) {
                throw new RuntimeException('Could not find Kind of AutoBlockAssignmentChildZeitblock: ' . $autoZeitblock->getId());
            }

            $zeitblock = $autoZeitblock->getZeitblock();
            if ($zeitblock === null) {
                throw new RuntimeException('Could not find Zeitblock of AutoBlockAssignmentChildZeitblock: ' . $autoZeitblock->getId());
            }

            // TODO silent? lot of mails could be going out
            $this->kontingentAcceptService->acceptKind($zeitblock, $kind);
        }

        $this->entityManager->remove($autoBlockAssignment);
    }
}
