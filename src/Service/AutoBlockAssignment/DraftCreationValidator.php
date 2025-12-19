<?php
declare(strict_types=1);

namespace App\Service\AutoBlockAssignment;

use App\Entity\Zeitblock;
use App\Service\AutoBlockAssignment\DraftCreationValidator\CapacityTracker;
use App\Service\AutoBlockAssignment\DraftCreationValidator\Result;
use function usort;

class DraftCreationValidator
{
    private array $addedCountByZeitblock;

    /**
     * @param Zeitblock[] $zeitblocks
     * @return array<Zeitblock[], Zeitblock[]>
     */
    public function validateZeitblocks(array $zeitblocks): array
    {
        $sortedZeitblocksByDay = $this->sortZeitblocksByDayAndTime($zeitblocks);

        $accepted = [];
        $warteschlange = [];
        foreach ($sortedZeitblocksByDay as $dayZeitblocks) {
            $this->validateDayZeitblocks($dayZeitblocks, $accepted, $warteschlange);
        }

        return [$accepted, $warteschlange];
    }

    /**
     * @param Zeitblock[] $dayZeitblocks
     * @param Zeitblock[] $accepted
     * @param Zeitblock[] $warteschlange
     */
    private function validateDayZeitblocks(array $dayZeitblocks, array &$accepted, array &$warteschlange): void
    {
        $lastAcceptedBis = null;

        foreach ($dayZeitblocks as $zeitblock) {
            // capacity check
            if (!$this->hasCapacity($zeitblock)) {
                $warteschlange[] = $zeitblock;
                continue;
            }

            // continuity check
            if ($lastAcceptedBis !== null && !$this->isContinuous($lastAcceptedBis, $zeitblock->getVon())) {
                $warteschlange[] = $zeitblock;
                continue;
            }

            $accepted[] = $zeitblock;
            $lastAcceptedBis = $zeitblock->getBis();

            if (isset($this->addedCountByZeitblock[$zeitblock->getId()])) {
                $this->addedCountByZeitblock[$zeitblock->getId()]++;
            } else {
                $this->addedCountByZeitblock[$zeitblock->getId()] = count($zeitblock->getKind()) + 1;
            }
        }
    }

    private function hasCapacity(Zeitblock $zeitblock): bool
    {
        $max = $zeitblock->getMax();
        if ($max === null) {
            return true;
        }

        $currentCount = $this->addedCountByZeitblock[$zeitblock->getId()] ?? count($zeitblock->getKind());

        return $currentCount < $max;
    }

    private function isContinuous(?\DateTimeInterface $lastBis, ?\DateTimeInterface $currentVon): bool
    {
        if ($lastBis === null || $currentVon === null) {
            return false;
        }

        return $lastBis->format('H:i:s') === $currentVon->format('H:i:s');
    }

    /**
     * @param Zeitblock[] $zeitblocks
     * @return array<int, Zeitblock[]>
     */
    private function sortZeitblocksByDayAndTime(array $zeitblocks): array
    {
        $sorted = [];
        foreach ($zeitblocks as $zeitblock) {
            $day = $zeitblock->getWochentag();
            $sorted[$day][] = $zeitblock;
        }

        foreach ($sorted as $day => $dayZeitblocks) {
            $sorted[$day] = $this->sortByTime($dayZeitblocks);
        }

        return $sorted;
    }

    /**
     * @param Zeitblock[] $zeitblocks
     * @return Zeitblock[]
     */
    private function sortByTime(array $zeitblocks): array
    {
        usort($zeitblocks, static function (Zeitblock $a, Zeitblock $b) {
            $vonA = $a->getVon();
            $vonB = $b->getVon();

            if ($vonA === null || $vonB === null) {
                return 0;
            }

            return $vonA <=> $vonB;
        });

        return $zeitblocks;
    }
}
