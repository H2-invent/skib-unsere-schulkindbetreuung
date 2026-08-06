<?php

declare(strict_types=1);

namespace App\Dto\Gebuehrenbescheid;

/**
 * One row of the Gebührenfestsetzung table: a care offering with the number of weekdays booked for it.
 *
 * The notice bills per offering ("Frühbetreuung", "Mittagsbetreuung", …) rather than per weekday, so this
 * aggregates the {@see FeeLine}s that share a label.
 */
final readonly class FeeAngebot
{
    public function __construct(
        public string $bezeichnung,
        public int $tage,
        public float $monatlich,
        public float $jaehrlich,
    ) {
    }
}
