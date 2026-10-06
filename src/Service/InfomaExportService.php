<?php

namespace App\Service;

use App\Entity\Kind;
use App\Entity\KinderRechnung;
use App\Entity\Rechnung;
use App\Entity\Sepa;
use DateTimeImmutable;

final class InfomaExportService
{
    private const CSV_DELIMITER = '|';

    public function generate(
        Sepa $sepa,
        string $externalId,
        string $sachkonto,
        string $kostenstelle,
        string $kostentraeger,
    ): string
    {
        $lines = [$this->generateKopfsatz($sepa, $externalId)];

        $counter = 1;

        foreach ($sepa->getRechnungen() as $rechnung) {
            if ($rechnung->getStammdaten() === null || $rechnung->getStammdaten()->getSepaInfo() !== true) {
                continue;
            }
            foreach ($rechnung->getKinderRechnungen() as $kinderRechnung) {
                if ($kinderRechnung->getKind() === null) {
                    continue;
                }
                if ($this->isZeroAmount($kinderRechnung->getSumme())) {
                    continue;
                }
                $lines[] = $this->generateFinanzbuchhaltungssatz($counter, $sepa, $kinderRechnung, $rechnung, $sachkonto, $kostenstelle, $kostentraeger);
                $lines[] = $this->generateGegenkontosatz($counter, $sepa, $kinderRechnung, $rechnung, $sachkonto, $kostenstelle, $kostentraeger);
                $counter++;
            }
        }

        return implode("\n", $lines) . "\n";
    }

    private function generateKopfsatz(Sepa $sepa, string $externalId): string
    {
        $kopfsatz = [
            'Satzart'                      => 'Kopf',
            'Extern-ID'                    => $externalId,
            'Laufende Nummer'              => $sepa->getId(),
            'Datum Erstellung'             => (new DateTimeImmutable('now'))->format('d.m.Y'),
            'Uhrzeit Erstellung'           => (new DateTimeImmutable('now'))->format('H:i:s'),
            'Art der Adressfortschreibung' => 'Automatisch',
            'Zusatzinfo-Code'              => '',
            'Zusatzinfo-Text'              => '',
            'Nur Adressfortschreibung'     => '',
        ];

        return implode(self::CSV_DELIMITER, $kopfsatz);
    }

    private function generateFinanzbuchhaltungssatz(
        int $counter,
        Sepa $sepa,
        KinderRechnung $kinderRechnung,
        Rechnung $rechnung,
        string $sachkonto,
        string $kostenstelle,
        string $kostentraeger,
    ): string
    {
        $rechnungsDatum = $rechnung->getCreatedAt();
        $kind = $kinderRechnung->getKind();
        $eltern = $rechnung->getStammdaten();

        $rechnungsDatum1 = $rechnungsDatum;
        $kind1 = $kind;
        $finanzbuchhaltungssatz = [
            'Satzart'                         => 'Finanzbuchhaltung',
            'Zeilennummer'                    => $counter * 10_000,
            'Belegart'                        => 'Rechnung',
            'Belegnummer'                     => $kinderRechnung->getId(),
            'Externe Belegnummer'             => '',
            'Storno'                          => '',
            'Belegdatum'                      => $rechnungsDatum->format('d.m.Y'),
            'Buchungsdatum'                   => $rechnungsDatum->format('d.m.Y'),
            'Kontoart'                        => 'Sachkonto',
            'Kontonummer'                     => $sachkonto,
            'Externe Kontonummer'             => '',
            'Buchungsart'                     => '',
            'MwSt. %'                         => '',
            'MwSt, Berechnungsart'            => 'Normale MwSt.',
            'Betrag'                          => $this->amount($kinderRechnung->getSumme()),
            'Beschreibung'                    => "Betreuungsentgelt_{$rechnungsDatum1->format('m/Y')}_{$kind1->getVorname()}_{$kind1->getNachname()}",
            'Währungscode'                    => 'EUR',
            'Fälligkeit'                      => '',
            'Skontodatum'                     => '',
            'Skonto %'                        => '',
            'Rechnungsart'                    => '',
            'Verkäufer/Einkäufer-Code'        => '',
            'Ausgleich mit Belegart'          => '',
            'Ausgleich mit Belegnr.'          => '',
            'Ausgleich mit ext. Belegnr.'     => '',
            'Kostenstellencode'               => $kostenstelle,
            'Kostenträgercode'                => $kostentraeger,
            'Menge'                           => '',
            'Zusatzinfo-Code'                 => '',
            'Zusatzinfo-Text'                 => '',
            'Gemeindenr.'                     => '01',
            'Adressnr.'                       => $eltern->getKundennummerForOrg($sepa->getOrganisation()?->getId())?->getKundennummer(),
            'Abgabenart'                      => '510',
            'Objektnr.'                       => '',
            'Veranlagungsjahr'                => '',
            'Abgabenart Objekt'               => '',
            'Lfd. Nr. Veranlagung'            => '',
            'Bankleitzahl'                    => '',
            'Bankkontonr.'                    => '',
            'Zahlungsformcode'                => 'ABBUCHUNG',
            'Kontoinhaber'                    => '',
            'Mahnart'                         => '',
            'Forderungsart'                   => '',
            'Mahnstufe'                       => '',
            'Nebenforderung zu Belegart'      => '',
            'Nebenforderung zu Belegnr.'      => '',
            'Nebenforderung zu Ext. Belegnr.' => '',
            'Vorabdotierungsnummer'           => '',
            'DocID'                           => '',
            'Investitionsnummer'              => '',
            'Erweiterter Belegtext'           => '',
            'Vorgangsnummer'                  => '',
            'Mahndatum'                       => '',
            'Shortcut Dimension 3 Code'       => '',
            'Shortcut Dimension 4 Code'       => '',
            'Shortcut Dimension 5 Code'       => '',
            'Shortcut Dimension 6 Code'       => '',
            'Shortcut Dimension 7 Code'       => '',
            'Shortcut Dimension 8 Code'       => '',
            'Kassenzeichen'                   => '',
            'Mahnmethodencode'                => '',
            'Abwarten'                        => '',
            'Ursachencode'                    => '',
            'Ausgleich mit Lfd. Nr.'          => '',
            'BIC'                             => $eltern->getBic(),
            'IBAN'                            => $eltern->getIban(),
            'Mandatsreferenz'                 => $this->generateMandatsreferenz($kind),
            'Unterschrift Mandat'             => '',
            'Ländercode Bank'                 => '',
            'Prenotifikationsdatum'           => '',
        ];

        return implode(self::CSV_DELIMITER, $finanzbuchhaltungssatz);
    }

    private function generateGegenkontosatz(
        int $counter,
        Sepa $sepa,
        KinderRechnung $kinderRechnung,
        Rechnung $rechnung,
        string $sachkonto,
        string $kostenstelle,
        string $kostentraeger,
    ): string
    {
        $rechnungsDatum = $rechnung->getCreatedAt();
        $kind = $kinderRechnung->getKind();
        $eltern = $rechnung->getStammdaten();

        $rechnungsDatum1 = $rechnungsDatum;
        $kind1 = $kind;
        $finanzbuchhaltungssatz = [
            'Satzart'                         => 'Gegenkonto',
            'Zeilennummer'                    => $counter * 10_000,
            'Belegart'                        => 'Rechnung',
            'Belegnummer'                     => $kinderRechnung->getId(),
            'Externe Belegnummer'             => '',
            'Kontoart'                        => 'Sachkonto',
            'Kontonummer'                     => $sachkonto,
            'Buchungsart'                     => 'Verkauf',
            'MwSt. %'                         => '',
            'MwSt, Berechnungsart'            => 'Normale MwSt.',
            'Betrag'                          => '-' . $this->amount($kinderRechnung->getSumme()),
            'Beschreibung'                    => "Betreuungsentgelt{$rechnungsDatum1->format('m.Y')}{$kind1->getVorname()}{$kind1->getNachname()}",
            'Kostenstellencode'               => $kostenstelle,
            'Kostenträgercode'                => $kostentraeger,
            'Menge'                           => '',
            'Zusatzinfo-Code'                 => '',
            'Zusatzinfo-Text'                 => '',
            'Gemeindenr.'                     => '01',
            'Adressnr.'                       => $eltern->getKundennummerForOrg($sepa->getOrganisation()?->getId())?->getKundennummer(),
            'Abgabenart'                      => '510',
            'Objektnr.'                       => '',
            'Veranlagungsjahr'                => '',
            'Abgabenart Objekt'               => '',
            'Lfd. Nr. Veranlagung'            => '',
            'Bankleitzahl'                    => '',
            'Bankkontonr.'                    => '',
            'Zahlungsformcode'                => '',
            'Kontoinhaber'                    => '',
            'Mahnart'                         => '',
            'Forderungsart'                   => '',
            'Mahnstufe'                       => '',
            'Nebenforderung zu Belegart'      => '',
            'Nebenforderung zu Belegnr.'      => '',
            'Nebenforderung zu Ext. Belegnr.' => '',
            'Vorabdotierungsnummer'           => '',
            'Reserviert'                      => '',
            'Investitionsnummer'              => '',
            'Erweiterter Belegtext'           => "Betreuungsentgelt_{$rechnungsDatum1->format('m/Y')}_{$kind1->getVorname()}_{$kind1->getNachname()}",
            'Shortcut Dimension 3 Code'       => '',
            'Shortcut Dimension 4 Code'       => '',
            'Shortcut Dimension 5 Code'       => '',
            'Shortcut Dimension 6 Code'       => '',
            'Shortcut Dimension 7 Code'       => '',
            'Shortcut Dimension 8 Code'       => '',
            'MwSt.-Geschäftsbuchungsgruppe'   => '',
            'MwSt.-Produktbuchungsgruppe'     => '',
        ];

        return implode(self::CSV_DELIMITER, $finanzbuchhaltungssatz);
    }

    private function amount(float $amount): string
    {
        return number_format($amount, 2, ',', '');
    }

    private function isZeroAmount(float $amount): bool
    {
        return round($amount, 2) === 0.0;
    }

    /**
     * Vorläufig: vorname nachname tracing id (auf 35 Zeichen abgeschnitten)
     */
    private function generateMandatsreferenz(Kind $kind): string
    {
        $reference = "{$kind->getVorname()}_{$kind->getNachname()}_{$kind->getTracing()}";

        return substr($reference, 0, 35);
    }
}
