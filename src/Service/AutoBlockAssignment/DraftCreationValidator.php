<?php

declare(strict_types=1);

namespace App\Service\AutoBlockAssignment;

use App\Entity\Zeitblock;
use App\Service\WidgetService;
use Symfony\Contracts\Service\ResetInterface;

class DraftCreationValidator implements ResetInterface
{
    private array $addedCountByZeitblock;

    public function __construct(
        private WidgetService $widgetService,
    ) {
    }

    public function reset(): void
    {
        $this->addedCountByZeitblock = [];
    }

    /**
     * @param Zeitblock[] $zeitblocks
     *
     * @return array<Zeitblock[], Zeitblock[]>
     */
    public function validateZeitblocks(array $zeitblocks, ?int $minBlocksPerDay): array
    {
        $sortedZeitblocksByDay = $this->sortZeitblocksByDayAndTime($zeitblocks);

        $accepted = [];
        $warteschlange = [];
        foreach ($sortedZeitblocksByDay as $dayZeitblocks) {
            $this->validateDayZeitblocks($dayZeitblocks, $accepted, $warteschlange, $minBlocksPerDay);
        }

        return [$accepted, $warteschlange];
    }

    /**
     * @param Zeitblock[] $zeitblocks
     *
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
     * @param Zeitblock[] $dayZeitblocks
     * @param Zeitblock[] $accepted
     * @param Zeitblock[] $warteschlange
     */
    private function validateDayZeitblocks(
        array $dayZeitblocks,
        array &$accepted,
        array &$warteschlange,
        ?int $minBlocksPerDay,
    ): void {
        $tempAccepted = [];
        $tempWarteschlange = [];

        $this->checkCapacity($dayZeitblocks, $tempAccepted, $tempWarteschlange);
        $this->checkVorgaenger($tempAccepted, $tempWarteschlange);
        $this->checkMinBlocksPerDay($minBlocksPerDay, $tempAccepted, $tempWarteschlange);
        $this->incrementAddedCount($tempAccepted);

        array_push($accepted, ...$tempAccepted);
        array_push($warteschlange, ...$tempWarteschlange);
    }

    /**
     * @param Zeitblock[] $zeitblocks
     *
     * @return Zeitblock[]
     */
    private function sortByTime(array $zeitblocks): array
    {
        \usort($zeitblocks, static function (Zeitblock $a, Zeitblock $b) {
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
     * @param Zeitblock[] $dayZeitblocks
     * @param Zeitblock[] $tempWarteschlange
     * @param Zeitblock[] $tempAccepted
     */
    private function checkCapacity(array $dayZeitblocks, array &$tempAccepted, array &$tempWarteschlange): void
    {
        foreach ($dayZeitblocks as $zeitblock) {
            if (!$this->hasCapacity($zeitblock)) {
                $tempWarteschlange[] = $zeitblock;
                continue;
            }

            $tempAccepted[] = $zeitblock;
        }
    }

    /**
     * @param Zeitblock[] $tempAccepted
     * @param Zeitblock[] $tempWarteschlange
     */
    private function checkVorgaenger(array &$tempAccepted, array &$tempWarteschlange): void
    {
        foreach ($tempAccepted as $key => $block) {
            $vorgaenger = $this->getAllVorgaengerBlocks($block);

            $vorgaengerIds = array_map(static fn ($v) => $v->getId(), $vorgaenger);
            $acceptedIds = array_map(static fn ($a) => $a->getId(), $tempAccepted);
            $allInAccepted = empty(array_diff($vorgaengerIds, $acceptedIds));

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
    private function checkMinBlocksPerDay(?int $minBlocksPerDay, array &$tempAccepted, array &$tempWarteschlange): void
    {
        if (count($tempAccepted) < $minBlocksPerDay) {
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

        $currentCount = $this->addedCountByZeitblock[$zeitblock->getId(
        )] ?? $this->widgetService->calcBlocksNumberNow($zeitblock);

        return $currentCount < $max;
    }

    /**
     * @return Zeitblock[]
     */
    private function getAllVorgaengerBlocks(Zeitblock $zeitblock): array
    {
        $allVorgaenger = [];
        $zeitblocks = array_unique([
            ...$zeitblock->getVorganger(),
            ...$zeitblock->getVorgangerSilent(),
        ]);
        foreach ($zeitblocks as $vorgaenger) {
            $allVorgaenger[] = $vorgaenger;
            array_push($allVorgaenger, ...$this->getAllVorgaengerBlocks($vorgaenger));
        }

        return array_unique($allVorgaenger);
    }
}
