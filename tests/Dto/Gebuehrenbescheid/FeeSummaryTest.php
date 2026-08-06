<?php

declare(strict_types=1);

namespace App\Tests\Dto\Gebuehrenbescheid;

use App\Dto\Gebuehrenbescheid\FeeLine;
use App\Dto\Gebuehrenbescheid\FeeSummary;
use PHPUnit\Framework\TestCase;

class FeeSummaryTest extends TestCase
{
    public function testHasLinesReflectsThePositions(): void
    {
        self::assertFalse($this->summary(0.0, 0.0, [])->hasLines());
        self::assertTrue($this->summary(42.0, 42.0, [$this->line(42.0)])->hasLines());
    }

    public function testTotalMatchingTheLineSumIsNotReportedAsDiffering(): void
    {
        $summary = $this->summary(84.0, 84.0, [$this->line(42.0), $this->line(42.0)]);

        self::assertFalse($summary->differsFromLineSum());
    }

    public function testSiblingDiscountIsReportedAsDiffering(): void
    {
        $summary = $this->summary(75.60, 84.0, [$this->line(42.0), $this->line(42.0)]);

        self::assertTrue($summary->differsFromLineSum());
    }

    /**
     * Rounding noise from the fee formula must not be advertised to parents as a discount.
     */
    public function testDifferenceBelowTheEpsilonIsIgnored(): void
    {
        self::assertFalse($this->summary(84.004, 84.0, [$this->line(84.0)])->differsFromLineSum());
        self::assertTrue($this->summary(84.01, 84.0, [$this->line(84.0)])->differsFromLineSum());
    }

    public function testAnnualTotalProjectsTheMonthlyFeeOverTheBilledMonths(): void
    {
        $summary = new FeeSummary(
            lines: [],
            gesamt: 75.60,
            summeDerPositionen: 84.00,
            einkommensklasse: null,
            stichtag: new \DateTimeImmutable('2026-08-01'),
            monate: 11,
        );

        self::assertSame(831.60, round($summary->jahresGesamt(), 2));
    }

    /**
     * A child whose school year cannot be resolved yields no month count, and must not silently report an
     * annual fee equal to a single month.
     */
    public function testAnnualTotalIsZeroWithoutBilledMonths(): void
    {
        self::assertSame(0.0, $this->summary(75.60, 75.60, [])->jahresGesamt());
    }

    /**
     * @param list<FeeLine> $lines
     */
    private function summary(float $gesamt, float $summeDerPositionen, array $lines): FeeSummary
    {
        return new FeeSummary(
            lines: $lines,
            gesamt: $gesamt,
            summeDerPositionen: $summeDerPositionen,
            einkommensklasse: null,
            stichtag: new \DateTimeImmutable('2026-08-01'),
        );
    }

    private function line(float $betrag): FeeLine
    {
        return new FeeLine(0, 'Montag', '07:15', '09:15', 'Ganztagsbetreuung', 'Musterschule', $betrag);
    }
}
