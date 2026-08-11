<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Active;
use App\Entity\Kind;
use App\Entity\Stammdaten;
use App\Repository\AbwesendRepository;
use App\Repository\AnwesenheitRepository;
use App\Repository\AutoBlockAssignmentChildRepository;
use App\Repository\ChildSickReportRepository;
use App\Repository\EmailResponseRepository;
use App\Repository\GeschwisterRepository;
use App\Repository\KindRepository;
use App\Repository\KundennummernRepository;
use App\Repository\PaymentRefundRepository;
use App\Repository\PaymentRepository;
use App\Repository\PersonenberechtigterRepository;
use App\Repository\RechnungRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Permanently removes personal data belonging to the children of one school year.
 *
 * Zeitblock entities are deliberately only detached and are never removed.
 */
final class DeleteSchoolYearChildrenService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly KindRepository $kindRepository,
        private readonly AbwesendRepository $abwesendRepository,
        private readonly AnwesenheitRepository $anwesenheitRepository,
        private readonly ChildSickReportRepository $childSickReportRepository,
        private readonly AutoBlockAssignmentChildRepository $autoBlockAssignmentChildRepository,
        private readonly RechnungRepository $rechnungRepository,
        private readonly KundennummernRepository $kundennummernRepository,
        private readonly EmailResponseRepository $emailResponseRepository,
        private readonly PersonenberechtigterRepository $personenberechtigterRepository,
        private readonly GeschwisterRepository $geschwisterRepository,
        private readonly PaymentRepository $paymentRepository,
        private readonly PaymentRefundRepository $paymentRefundRepository,
    ) {
    }

    public function deleteForSchoolYear(Active $schoolYear): int
    {
        return $this->entityManager->wrapInTransaction(function () use ($schoolYear): int {
            $children = $this->kindRepository->findBySchoolYear($schoolYear);
            /** @var array<int, Stammdaten> $parents */
            $parents = [];

            foreach ($children as $child) {
                $parent = $child->getEltern();
                if ($parent !== null) {
                    $parents[$parent->getId()] = $parent;
                }

                $this->removeChildData($child);
                $this->entityManager->remove($child);
            }

            $this->entityManager->flush();

            foreach ($parents as $parent) {
                // A parent record may be shared with a child in another school year.
                // In that case it must not be removed along with unrelated data.
                if ($this->kindRepository->count(['eltern' => $parent]) === 0) {
                    $this->removeParentData($parent);
                    $this->entityManager->remove($parent);
                }
            }

            $this->entityManager->flush();

            return count($children);
        });
    }

    private function removeChildData(Kind $child): void
    {
        foreach ($child->getZeitblocks()->toArray() as $block) {
            $block->removeKind($child);
        }
        foreach ($child->getBeworben()->toArray() as $block) {
            $child->removeBeworben($block);
        }
        foreach ($child->getWarteliste()->toArray() as $block) {
            $child->removeWarteliste($block);
        }
        foreach ($child->getMovedToWaiting()->toArray() as $block) {
            $child->removeMovedToWaiting($block);
        }

        foreach ($child->getRechnungen()->toArray() as $invoice) {
            $this->entityManager->remove($invoice);
        }

        $childDataRepositories = [
            $this->abwesendRepository,
            $this->anwesenheitRepository,
            $this->childSickReportRepository,
        ];

        foreach ($childDataRepositories as $repository) {
            foreach ($repository->findBy(['kind' => $child]) as $entity) {
                $this->entityManager->remove($entity);
            }
        }

        $assignment = $this->autoBlockAssignmentChildRepository->findOneBy(['kind' => $child]);
        if ($assignment !== null) {
            // AutoBlockAssignmentChild cascades the removal to all of its
            // AutoBlockAssignmentChildZeitblock entities.
            $this->entityManager->remove($assignment);
        }
    }

    private function removeParentData(Stammdaten $parent): void
    {
        $parentDataRepositories = [
            $this->rechnungRepository,
            $this->kundennummernRepository,
            $this->emailResponseRepository,
            $this->personenberechtigterRepository,
            $this->geschwisterRepository,
        ];

        foreach ($parentDataRepositories as $repository) {
            foreach ($repository->findBy(['stammdaten' => $parent]) as $entity) {
                $this->entityManager->remove($entity);
            }
        }

        foreach ($this->paymentRepository->findBy(['stammdaten' => $parent]) as $payment) {
            foreach ($this->paymentRefundRepository->findBy(['payment' => $payment]) as $refund) {
                $this->entityManager->remove($refund);
            }
            $this->entityManager->remove($payment);
        }
    }
}
