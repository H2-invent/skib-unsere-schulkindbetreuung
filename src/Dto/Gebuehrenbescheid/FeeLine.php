<?php

declare(strict_types=1);

namespace App\Dto\Gebuehrenbescheid;

/**
 * One chargeable Zeitblock as it appears on a Gebührenbescheid.
 *
 * Deliberately flattened to scalars: the PDF preview builds fixtures that are not persisted and whose
 * Zeitblocks have neither a Schule nor an Active, so handing the entities themselves to a city-authored
 * template would break as soon as it touched block.schule.name or block.active.bis.
 */
final readonly class FeeLine
{
    public function __construct(
        public int $wochentag,
        public string $wochentagLabel,
        public string $von,
        public string $bis,
        public string $artLabel,
        public ?string $schule,
        public float $betrag,
    ) {
    }
}
