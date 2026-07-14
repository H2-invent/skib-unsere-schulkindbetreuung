<?php
declare(strict_types=1);

namespace App\Service\AutoBlockAssignment;

use App\Entity\Zeitblock;
use App\Service\WidgetService;
use Symfony\Contracts\Service\ResetInterface;
use function usort;

class DraftCreationValidator implements ResetInterface
{
    private array $addedCountByZeitblock;

    public function __construct(private WidgetService $widgetService)
    {
    }

    public function reset(): void
    {
        $this->addedCountByZeitblock = [];
    }

    /**
     * @param Zeitblock[] $appliedZeitblocks
     * @param Zeitblock[] $bookedZeitblocks
     * @return array<Zeitblock[], Zeitblock[]>
     */
    public function validateZeitblocks(array $appliedZeitblocks, array $bookedZeitblocks, ?int $minBlocksPerDay, ?int $minDaysPerWeek): array
    {
        $sortedZeitblocksByDay = $this->sortZeitblocksByDayAndTime($appliedZeitblocks);
        $sortedBookedZeitblocksByDay = $this->sortZeitblocksByDayAndTime($bookedZeitblocks);

        if (!$this->checkMinDaysPerWeek($minDaysPerWeek, $sortedZeitblocksByDay, $sortedBookedZeitblocksByDay)) {
            return [[], $appliedZeitblocks];
        }

        $accepted = [];
        $warteschlange = [];
        foreach ($sortedZeitblocksByDay as $weekday => $dayZeitblocks) {
            $this->validateDayZeitblocks($dayZeitblocks, $sortedBookedZeitblocksByDay[$weekday] ?? [], $accepted, $warteschlange, $minBlocksPerDay);
        }

        if (!$this->checkMinDaysPerWeekAccepted($minDaysPerWeek, $accepted, $bookedZeitblocks)) {
            return [[], $appliedZeitblocks];
        }

        return [$accepted, $warteschlange];
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

    /**
     * @param array<int, Zeitblock[]> $sortedZeitblocksByDay
     * @param array<int, Zeitblock[]> $sortedBookedZeitblocksByDay
     */
    private function checkMinDaysPerWeek(?int $minDaysPerWeek, array $sortedZeitblocksByDay, array $sortedBookedZeitblocksByDay): bool
    {
        if ($minDaysPerWeek === null) {
            return true;
        }
        $daysApplied = array_keys($sortedZeitblocksByDay);
        $daysBooked = array_keys($sortedBookedZeitblocksByDay);
        $weekdays = array_merge($daysApplied, $daysBooked);
        $weekdays = array_unique($weekdays);

        return count($weekdays) >= $minDaysPerWeek;
    }

    /**
     * @param Zeitblock[] $accepted
     * @param Zeitblock[] $booked
     */
    private function checkMinDaysPerWeekAccepted(?int $minDaysPerWeek, array $accepted, array $booked): bool
    {
        if ($minDaysPerWeek === null) {
            return true;
        }
        $zeitblocks = array_merge($accepted, $booked);
        $weekdays = array_map(static fn(Zeitblock $block) => $block->getWochentag(), $zeitblocks);
        $weekdays = array_unique($weekdays);
        $weekdays = array_filter($weekdays, static fn ($weekday) => $weekday !== null);

        return count($weekdays) >= $minDaysPerWeek;
    }

    /**
     * @param Zeitblock[] $dayZeitblocks
     * @param Zeitblock[] $bookedZeitblocks
     * @param Zeitblock[] $accepted
     * @param Zeitblock[] $warteschlange
     */
    private function validateDayZeitblocks(
        array $dayZeitblocks,
        array $bookedZeitblocks,
        array &$accepted,
        array &$warteschlange,
        ?int $minBlocksPerDay
    ): void
    {
        $tempAccepted = $dayZeitblocks;
        $tempWarteschlange = [];

        $this->checkCapacity($tempAccepted, $tempWarteschlange);

        // loop these checks until the result is stable because they may depend on each other
        // 10 loops should be more than enough
        foreach (range(1, 10) as $i) {
            $prevAccepted = $tempAccepted;
            $prevWarteschlange = $tempWarteschlange;

            $this->checkVorgaenger($tempAccepted, $tempWarteschlange, $bookedZeitblocks);
            $this->checkSilentVorgaenger($tempAccepted, $tempWarteschlange);
            $this->checkMinBlocksPerDay($minBlocksPerDay, $tempAccepted, $tempWarteschlange, $bookedZeitblocks);

            if ($tempAccepted === $prevAccepted && $tempWarteschlange === $prevWarteschlange) {
                break;
            }
        }

        $this->incrementAddedCount($tempAccepted);

        array_push($accepted, ...$tempAccepted);
        array_push($warteschlange, ...$tempWarteschlange);
    }

    /**
     * @param Zeitblock[] $tempAccepted
     * @param Zeitblock[] $tempWarteschlange
     */
    private function checkCapacity(array &$tempAccepted, array &$tempWarteschlange): void
    {
        foreach ($tempAccepted as $key => $zeitblock) {
            if (!$this->hasCapacity($zeitblock)) {
                unset($tempAccepted[$key]);
                $tempWarteschlange[] = $zeitblock;
            }
        }
    }

    /**
     * @param Zeitblock[] $tempAccepted
     * @param Zeitblock[] $tempWarteschlange
     * @param Zeitblock[] $bookedZeitblocks
     */
    private function checkVorgaenger(array &$tempAccepted, array &$tempWarteschlange, array $bookedZeitblocks): void
    {
        $acceptedAndBooked = array_merge($tempAccepted, $bookedZeitblocks);
        $acceptedAndBookedIds = array_map(static fn($a) => $a->getId(), $acceptedAndBooked);

        foreach ($tempAccepted as $key => $block) {
            $vorgaenger = $this->getAllVorgaengerBlocks($block);
            if (count($vorgaenger) === 0) {
                continue;
            }

            $vorgaengerIds = array_map(static fn($v) => $v->getId(), $vorgaenger);
            $allInAccepted = empty(array_diff($vorgaengerIds, $acceptedAndBookedIds));

            if (!$allInAccepted) {
                unset($tempAccepted[$key]);
                $tempWarteschlange[] = $block;
            }
        }
    }

    /**
     * @param Zeitblock[] $tempAccepted
     * @param Zeitblock[] $tempWarteschlange
     */
    private function checkSilentVorgaenger(array &$tempAccepted, array &$tempWarteschlange): void
    {
        foreach ($tempAccepted as $key => $block) {
            $silentVorgaenger = $block->getVorgangerSilent();
            foreach ($silentVorgaenger as $vorgaenger) {
                // remove block from accepted if user tried to book it and it failed to accept
                if (in_array($vorgaenger, $tempWarteschlange, true)) {
                    unset($tempAccepted[$key]);
                    $tempWarteschlange[] = $block;
                }
            }
        }
    }

    /**
     * @param int|null $minBlocksPerDay
     * @param Zeitblock[] $tempAccepted
     * @param Zeitblock[] $tempWarteschlange
     * @param Zeitblock[] $bookedZeitblocks
     */
    private function checkMinBlocksPerDay(?int $minBlocksPerDay, array &$tempAccepted, array &$tempWarteschlange, array $bookedZeitblocks): void
    {
        $acceptedAndBooked = array_merge($tempAccepted, $bookedZeitblocks);
        if (count($acceptedAndBooked) < $minBlocksPerDay) {
            $tempWarteschlange = array_merge($tempAccepted, $tempWarteschlange);
            $tempAccepted = [];
        }
    }

    /**
     * @param Zeitblock[] $tempAccepted
     */
    private function incrementAddedCount(array $tempAccepted): void
    {
        foreach ($tempAccepted as $block) {
            if (isset($this->addedCountByZeitblock[$block->getId()])) {
                $this->addedCountByZeitblock[$block->getId()]++;
            } else {
                $this->addedCountByZeitblock[$block->getId()] = $this->widgetService->calcBlocksNumberNow($block) + 1;
            }
        }
    }

    private function hasCapacity(Zeitblock $zeitblock): bool
    {
        $max = $zeitblock->getMax();
        if ($max === null) {
            return true;
        }

        $currentCount = $this->addedCountByZeitblock[$zeitblock->getId()] ?? $this->widgetService->calcBlocksNumberNow($zeitblock);

        return $currentCount < $max;
    }

    /**
     * @return Zeitblock[]
     */
    private function getAllVorgaengerBlocks(Zeitblock $zeitblock): array
    {
        $allVorgaenger = [];
        $zeitblocks = array_unique($zeitblock->getVorganger()->toArray());

        foreach ($zeitblocks as $vorgaenger) {
            $allVorgaenger[] = $vorgaenger;
            array_push($allVorgaenger, ...$this->getAllVorgaengerBlocks($vorgaenger));
        }

        return array_unique($allVorgaenger);
    }
}
