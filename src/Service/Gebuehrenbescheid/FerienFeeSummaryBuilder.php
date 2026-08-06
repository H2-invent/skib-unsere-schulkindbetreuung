<?php

declare(strict_types=1);

namespace App\Service\Gebuehrenbescheid;

use App\Dto\Gebuehrenbescheid\FerienFeeLine;
use App\Dto\Gebuehrenbescheid\FerienFeeSummary;
use App\Entity\Kundennummern;
use App\Entity\KindFerienblock;
use App\Entity\Stammdaten;

/**
 * Assembles the fee figures for a holiday-programme fee notice.
 *
 * Deliberately does not involve BerechnungsService: a Ferienblock booking stores the price it was booked at
 * (KindFerienblock::getPreis()), which is also what CheckoutPaymentService charges, so the notice is a plain
 * sum over the household's booked blocks.
 */
final class FerienFeeSummaryBuilder
{
    public function build(Stammdaten $stammdaten): FerienFeeSummary
    {
        $lines = [];
        foreach ($stammdaten->getKinds() as $kind) {
            // getKindFerienblocksGebucht() already restricts to state 10 ("Gebucht"), so cancelled and
            // waiting-list bookings never reach the notice.
            foreach ($kind->getKindFerienblocksGebucht() as $booking) {
                /** @var KindFerienblock $booking */
                $ferienblock = $booking->getFerienblock();
                if ($ferienblock === null) {
                    continue;
                }

                $lines[] = new FerienFeeLine(
                    kind: trim($kind->getVorname() . ' ' . $kind->getNachname()),
                    titel: (string) $ferienblock->translate()->getTitel(),
                    von: $this->toImmutable($ferienblock->getStartDate()),
                    bis: $this->toImmutable($ferienblock->getEndDate()),
                    ort: $ferienblock->getOrt(),
                    betrag: (float) $booking->getPreis(),
                );
            }
        }

        usort(
            $lines,
            static fn (FerienFeeLine $a, FerienFeeLine $b): int => [$a->kind, $a->von] <=> [$b->kind, $b->von],
        );

        return new FerienFeeSummary(
            lines: array_values($lines),
            gesamt: array_sum(array_map(static fn (FerienFeeLine $line): float => $line->betrag, $lines)),
            stichtag: new \DateTimeImmutable(),
            kundennummer: $this->findKundennummer($stammdaten),
        );
    }

    private function toImmutable(?\DateTimeInterface $date): ?\DateTimeImmutable
    {
        return $date !== null ? \DateTimeImmutable::createFromInterface($date) : null;
    }

    /**
     * A household can hold customer numbers at several providers; for a holiday notice any of them is a better
     * reference than none, so the first is used.
     */
    private function findKundennummer(Stammdaten $stammdaten): ?string
    {
        foreach ($stammdaten->getKundennummerns() as $kundennummer) {
            /** @var Kundennummern $kundennummer */
            if ($kundennummer->getKundennummer() !== null) {
                return $kundennummer->getKundennummer();
            }
        }

        return null;
    }
}
