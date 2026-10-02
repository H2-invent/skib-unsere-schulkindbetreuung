<?php

declare(strict_types=1);

namespace App\Tests\Dto;

use App\Dto\ChildFeeCalculation;
use PHPUnit\Framework\TestCase;

final class ChildFeeCalculationTest extends TestCase
{
    public function testItExposesNetGrossAndDiscountAmounts(): void
    {
        $calculation = new ChildFeeCalculation(summe: 70.0, bruttoSumme: 80.0, rabatt: 10.0);

        self::assertSame(70.0, $calculation->summe);
        self::assertSame(80.0, $calculation->bruttoSumme);
        self::assertSame(10.0, $calculation->rabatt);
    }
}
