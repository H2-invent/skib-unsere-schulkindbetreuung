<?php

declare(strict_types=1);

namespace App\Dto\Gebuehrenbescheid;

/**
 * The fee figures for a holiday-programme fee notice.
 *
 * Much simpler than {@see FeeSummary}: holiday bookings carry the price that was actually booked
 * (KindFerienblock::getPreis()), so the total is a plain sum with no calculation formula involved and
 * therefore no discrepancy between the line items and the total.
 */
final readonly class FerienFeeSummary
{
    /**
     * @param list<FerienFeeLine> $lines
     */
    public function __construct(
        public array $lines,
        public float $gesamt,
        public \DateTimeImmutable $stichtag,
        public ?string $kundennummer = null,
    ) {
    }

    public function hasLines(): bool
    {
        return $this->lines !== [];
    }
}
