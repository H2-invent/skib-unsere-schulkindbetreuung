<?php

declare(strict_types=1);

namespace App\Dto\Gebuehrenbescheid;

/**
 * One booked Ferienblock as it appears on the holiday-programme fee notice.
 *
 * Flattened to scalars for the same reason as {@see FeeLine}: the admin preview builds these by hand rather
 * than from persisted entities.
 */
final readonly class FerienFeeLine
{
    public function __construct(
        public string $kind,
        public string $titel,
        public ?\DateTimeImmutable $von,
        public ?\DateTimeImmutable $bis,
        public ?string $ort,
        public float $betrag,
    ) {
    }
}
