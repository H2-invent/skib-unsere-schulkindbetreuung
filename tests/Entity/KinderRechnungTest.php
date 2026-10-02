<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Kind;
use App\Entity\KinderRechnung;
use App\Entity\Rechnung;
use PHPUnit\Framework\TestCase;

final class KinderRechnungTest extends TestCase
{
    public function testInvoiceKeepsACompleteChildSpecificBreakdown(): void
    {
        $child = (new Kind())->setVorname('Anna')->setNachname('Mustermann');
        $childInvoice = (new KinderRechnung())
            ->setKind($child)
            ->setBruttoSumme(80.0)
            ->setRabatt(10.0)
            ->setSumme(70.0);
        $invoice = (new Rechnung())->addKinderRechnung($childInvoice);

        self::assertSame($invoice, $childInvoice->getRechnung());
        self::assertSame($child, $childInvoice->getKind());
        self::assertTrue($child->getKinderRechnungen()->contains($childInvoice));
        self::assertSame(80.0, $childInvoice->getBruttoSumme());
        self::assertSame(10.0, $childInvoice->getRabatt());
        self::assertSame(70.0, $childInvoice->getSumme());
    }

    public function testMoneyValuesArePersistedAtCentPrecision(): void
    {
        $childInvoice = (new KinderRechnung())
            ->setBruttoSumme(10.006)
            ->setRabatt(1.005)
            ->setSumme(9.001);

        self::assertSame(10.01, $childInvoice->getBruttoSumme());
        self::assertSame(1.01, $childInvoice->getRabatt());
        self::assertSame(9.0, $childInvoice->getSumme());
    }
}
