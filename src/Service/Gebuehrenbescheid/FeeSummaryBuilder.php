<?php

declare(strict_types=1);

namespace App\Service\Gebuehrenbescheid;

use App\Dto\Gebuehrenbescheid\FeeAngebot;
use App\Dto\Gebuehrenbescheid\FeeLine;
use App\Dto\Gebuehrenbescheid\FeeSummary;
use App\Entity\Kind;
use App\Entity\Kundennummern;
use App\Entity\Stammdaten;
use App\Entity\Zeitblock;
use App\Repository\KindRepository;
use App\Service\BerechnungsService;
use App\Service\ElternService;
use Psr\Log\LoggerInterface;

/**
 * Assembles the fee figures for a Gebührenbescheid.
 *
 * Only confirmed blocks count: a fee notice must not bill blocks the child has merely applied for, which is
 * why the per-line loop reads getZeitblocks() and the total is requested with $withBeworben = false.
 */
final class FeeSummaryBuilder
{
    public function __construct(
        private readonly BerechnungsService $berechnungsService,
        private readonly ElternService $elternService,
        private readonly KindRepository $kindRepository,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function build(Kind $kind, ?\DateTimeInterface $stichtag = null): FeeSummary
    {
        $stichtag ??= $kind->getStartDate() ?? new \DateTime();
        // BerechnungsService, ElternService and KindRepository all declare \DateTime rather than the interface.
        $stichtagDt = \DateTime::createFromInterface($stichtag);

        $eltern = $this->elternService->getElternForSpecificTimeAndKind($kind, $stichtagDt) ?? $kind->getEltern();
        // findLatestKindForDate() is nullable, and BerechnungsService would fatal on a null Kind.
        $resolvedKind = $this->kindRepository->findLatestKindForDate($kind, $stichtagDt) ?? $kind;

        // getEinkommen() is nullable and $preise[null] is not $preise[0].
        $einkommen = $eltern?->getEinkommen() ?? 0;

        $lines = $this->buildLines($resolvedKind, $einkommen);
        $summeDerPositionen = array_sum(array_map(static fn (FeeLine $line): float => $line->betrag, $lines));

        $berechnungFehlgeschlagen = false;
        try {
            $gesamt = $this->berechnungsService->getPreisforBetreuung($kind, false, $stichtagDt);
        } catch (\Throwable $exception) {
            // The total is produced by eval()-ing a city-authored formula, so any error class is possible.
            $this->logger->error('Gebuehrenbescheid: fee calculation failed, falling back to the line sum', [
                'kind' => $kind->getId(),
                'exception' => $exception,
            ]);
            $gesamt = $summeDerPositionen;
            $berechnungFehlgeschlagen = true;
        }

        $gehaltsklassen = $resolvedKind->getSchule()?->getStadt()?->getGehaltsklassen() ?? [];

        $schuljahr = $resolvedKind->getSchuljahr();
        $von = $schuljahr?->getVon();
        $bis = $schuljahr?->getBis();
        $monate = $this->countBilledMonths($von, $bis);

        return new FeeSummary(
            lines: $lines,
            gesamt: (float) $gesamt,
            summeDerPositionen: $summeDerPositionen,
            einkommensklasse: $gehaltsklassen[$einkommen] ?? null,
            stichtag: \DateTimeImmutable::createFromInterface($stichtagDt),
            kundennummer: $this->findKundennummer($eltern, $resolvedKind),
            berechnungFehlgeschlagen: $berechnungFehlgeschlagen,
            angebote: $this->groupByAngebot($lines, $monate),
            monate: $monate,
            faelligkeiten: $this->buildDueDates($von, $monate),
            geschwisterAnzahl: $eltern?->getGeschwisters()->count() ?? 0,
            zeitraumVon: $von !== null ? \DateTimeImmutable::createFromInterface($von) : null,
            zeitraumBis: $bis !== null ? \DateTimeImmutable::createFromInterface($bis) : null,
            schuljahr: $this->formatSchuljahr($von, $bis),
        );
    }

    /**
     * Aggregates the per-weekday lines into the offering rows the notice bills by.
     *
     * @param list<FeeLine> $lines
     *
     * @return list<FeeAngebot>
     */
    private function groupByAngebot(array $lines, int $monate): array
    {
        $grouped = [];
        foreach ($lines as $line) {
            $key = $line->artLabel;
            $grouped[$key] ??= ['tage' => 0, 'monatlich' => 0.0];
            ++$grouped[$key]['tage'];
            $grouped[$key]['monatlich'] += $line->betrag;
        }

        $angebote = [];
        foreach ($grouped as $bezeichnung => $data) {
            $angebote[] = new FeeAngebot(
                bezeichnung: (string) $bezeichnung,
                tage: $data['tage'],
                monatlich: $data['monatlich'],
                jaehrlich: $data['monatlich'] * $monate,
            );
        }

        return $angebote;
    }

    /**
     * Number of months the school year spans, inclusive of its first and last month.
     *
     * The application stores no "billed months" rule, so the school year's own care period is the only
     * available basis. A city whose statute bills fewer months has to adjust its template.
     */
    private function countBilledMonths(?\DateTimeInterface $von, ?\DateTimeInterface $bis): int
    {
        if ($von === null || $bis === null || $bis < $von) {
            return 0;
        }

        $months = ((int) $bis->format('Y') - (int) $von->format('Y')) * 12
            + ((int) $bis->format('n') - (int) $von->format('n'));

        return $months + 1;
    }

    /**
     * @return list<\DateTimeImmutable> the first of each billed month
     */
    private function buildDueDates(?\DateTimeInterface $von, int $monate): array
    {
        if ($von === null || $monate < 1) {
            return [];
        }

        $first = \DateTimeImmutable::createFromInterface($von)->modify('first day of this month')->setTime(0, 0);

        $dates = [];
        for ($i = 0; $i < $monate; ++$i) {
            $dates[] = $first->modify(sprintf('+%d months', $i));
        }

        return $dates;
    }

    private function formatSchuljahr(?\DateTimeInterface $von, ?\DateTimeInterface $bis): ?string
    {
        if ($von === null || $bis === null) {
            return null;
        }

        $vonYear = $von->format('Y');
        $bisYear = $bis->format('Y');

        return $vonYear === $bisYear ? $vonYear : $vonYear . '/' . $bisYear;
    }

    /**
     * The household's customer number at the organisation looking after this child (the Debitorennummer).
     */
    private function findKundennummer(?Stammdaten $eltern, Kind $kind): ?string
    {
        $organisation = $kind->getSchule()?->getOrganisation();
        if ($eltern === null || $organisation === null) {
            return null;
        }

        foreach ($eltern->getKundennummerns() as $kundennummer) {
            /** @var Kundennummern $kundennummer */
            if ($kundennummer->getOrganisation() === $organisation) {
                return $kundennummer->getKundennummer();
            }
        }

        return null;
    }

    /**
     * @return list<FeeLine>
     */
    private function buildLines(Kind $kind, int $einkommen): array
    {
        $blocks = array_filter(
            $kind->getZeitblocks()->toArray(),
            // Mirrors BerechnungsService::getBetragforKindBetreuung(): Mittagessen (ganztag 0) is not billed
            // through the block price, and deleted blocks never are.
            static fn (Zeitblock $block): bool => $block->getGanztag() !== 0 && $block->getDeleted() === false,
        );

        usort(
            $blocks,
            static fn (Zeitblock $a, Zeitblock $b): int => [$a->getWochentag(), $a->getVon()]
                <=> [$b->getWochentag(), $b->getVon()],
        );

        return array_values(array_map(
            fn (Zeitblock $block): FeeLine => new FeeLine(
                wochentag: (int) $block->getWochentag(),
                wochentagLabel: (string) $block->getWochentagString(),
                von: $block->getVon()?->format('H:i') ?? '',
                bis: $block->getBis()?->format('H:i') ?? '',
                artLabel: $this->angebotLabel($block),
                schule: $block->getSchule()?->getName(),
                betrag: (float) ($block->getPreise()[$einkommen] ?? 0),
            ),
            $blocks,
        ));
    }

    /**
     * The name of the care offering as the city labelled it, falling back to the generic Ganztag wording.
     */
    private function angebotLabel(Zeitblock $block): string
    {
        try {
            $bezeichnung = $block->translate()->getBlockbezeichnung();
            if (is_string($bezeichnung) && trim($bezeichnung) !== '') {
                return trim($bezeichnung);
            }
        } catch (\Throwable) {
            // Blocks that were never persisted (the preview fixtures) have neither translations nor a current
            // locale for Doctrine-Behaviors to resolve.
        }

        return (string) $block->getGanztagString();
    }
}
