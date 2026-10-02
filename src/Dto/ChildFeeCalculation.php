<?php

declare(strict_types=1);

namespace App\Dto;

final readonly class ChildFeeCalculation
{
    public function __construct(
        public float $summe,
        public float $bruttoSumme,
        public float $rabatt,
    ) {
    }
}
