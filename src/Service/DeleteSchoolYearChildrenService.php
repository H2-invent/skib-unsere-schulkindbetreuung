<?php

namespace App\Service;

use App\Entity\Abwesend;
use App\Entity\Active;
use App\Entity\Anwesenheit;
use App\Entity\AutoBlockAssignmentChild;
use App\Entity\ChildSickReport;
use App\Entity\EmailResponse;
use App\Entity\Kind;
use App\Entity\Kundennummern;
use App\Entity\Payment;
use App\Entity\PaymentRefund;
use App\Entity\Personenberechtigter;
use App\Entity\Geschwister;
use App\Entity\Rechnung;
use App\Entity\Stammdaten;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Permanently removes personal data belonging to the children of one school year.
 *
 * Zeitblock entities are deliberately only detached and are never removed.
 */
class DeleteSchoolYearChildrenService
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function delete(Active $schoolYear): int
    {
        return $this->entityManager->wrapInTransaction(function () use ($schoolYear): int {
            $children = $this->findChildren($schoolYear);
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
                if ($this->entityManager->getRepository(Kind::class)->count(['eltern' => $parent]) === 0) {
                    $this->removeParentData($parent);
                    $this->entityManager->remove($parent);
                }
            }

            $this->entityManager->flush();

            return count($children);
        });
    }

    /** @return Kind[] */
    private function findChildren(Active $schoolYear): array
    {
        return $this->entityManager->createQueryBuilder()
            ->select('DISTINCT child')
            ->from(Kind::class, 'child')
            ->leftJoin('child.zeitblocks', 'booked')
            ->leftJoin('child.beworben', 'applied')
            ->leftJoin('child.warteliste', 'waiting')
            ->leftJoin('child.movedToWaiting', 'moved')
            ->where('booked.active = :schoolYear')
            ->orWhere('applied.active = :schoolYear')
            ->orWhere('waiting.active = :schoolYear')
            ->orWhere('moved.active = :schoolYear')
            ->setParameter('schoolYear', $schoolYear)
            ->getQuery()
            ->getResult();
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

        foreach ([Abwesend::class, Anwesenheit::class, ChildSickReport::class] as $entityClass) {
            foreach ($this->entityManager->getRepository($entityClass)->findBy(['kind' => $child]) as $entity) {
                $this->entityManager->remove($entity);
            }
        }

        $assignment = $this->entityManager->getRepository(AutoBlockAssignmentChild::class)->findOneBy(['kind' => $child]);
        if ($assignment !== null) {
            $this->entityManager->remove($assignment);
        }
    }

    private function removeParentData(Stammdaten $parent): void
    {
        foreach ([Rechnung::class, Kundennummern::class, EmailResponse::class, Personenberechtigter::class, Geschwister::class] as $entityClass) {
            foreach ($this->entityManager->getRepository($entityClass)->findBy(['stammdaten' => $parent]) as $entity) {
                $this->entityManager->remove($entity);
            }
        }

        foreach ($this->entityManager->getRepository(Payment::class)->findBy(['stammdaten' => $parent]) as $payment) {
            foreach ($this->entityManager->getRepository(PaymentRefund::class)->findBy(['payment' => $payment]) as $refund) {
                $this->entityManager->remove($refund);
            }
            $this->entityManager->remove($payment);
        }
    }
}
