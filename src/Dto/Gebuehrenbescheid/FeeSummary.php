<?php

declare(strict_types=1);

namespace App\Dto\Gebuehrenbescheid;

/**
 * The computed fee figures handed to a city-authored Gebührenbescheid template.
 *
 * Which number is authoritative matters here, because there are two of them:
 *
 * - {@see self::$gesamt} is the result of the city's own Berechnungsformel (or the school year's
 *   specialCalculationFormular), i.e. exactly the amount parents are shown everywhere else in the
 *   application. This is the amount to print on the notice.
 * - {@see self::$summeDerPositionen} is the naive sum of {@see self::$lines}. It legitimately differs from
 *   $gesamt whenever the formula applies sibling discounts, caps or income-based reductions.
 *
 * {@see self::differsFromLineSum()} exists so a template can print the difference as an "Ermäßigung" row
 * rather than showing two contradictory totals.
 */
final readonly class FeeSummary
{
    /**
     * @param list<FeeLine>              $lines         one entry per booked weekday
     * @param list<FeeAngebot>           $angebote      the same fees grouped by care offering
     * @param list<\DateTimeImmutable>   $faelligkeiten one due date per billed month
     */
    public function __construct(
        public array $lines,
        public float $gesamt,
        public float $summeDerPositionen,
        public ?string $einkommensklasse,
        public \DateTimeImmutable $stichtag,
        public ?string $kundennummer = null,
        public bool $berechnungFehlgeschlagen = false,
        public array $angebote = [],
        public int $monate = 0,
        public array $faelligkeiten = [],
        public int $geschwisterAnzahl = 0,
        public ?\DateTimeImmutable $zeitraumVon = null,
        public ?\DateTimeImmutable $zeitraumBis = null,
        public ?string $schuljahr = null,
    ) {
    }

    public function hasLines(): bool
    {
        return $this->lines !== [];
    }

    public function differsFromLineSum(): bool
    {
        return abs($this->gesamt - $this->summeDerPositionen) > 0.005;
    }

    /**
     * The authoritative monthly fee projected over the billed months of the school year.
     */
    public function jahresGesamt(): float
    {
        return $this->gesamt * $this->monate;
    }
}
