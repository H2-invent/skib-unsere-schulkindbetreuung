<?php

declare(strict_types=1);

namespace App\Service\TemplatePreview;

use App\Dto\Gebuehrenbescheid\FeeAngebot;
use App\Dto\Gebuehrenbescheid\FeeLine;
use App\Dto\Gebuehrenbescheid\FeeSummary;
use App\Dto\Gebuehrenbescheid\FerienFeeLine;
use App\Dto\Gebuehrenbescheid\FerienFeeSummary;
use App\Dto\TemplatePreview\PreviewFixture;
use App\Entity\Kind;
use App\Entity\Organisation;
use App\Entity\Stammdaten;
use App\Entity\Zeitblock;

/**
 * Builds the sample data city admins see when they preview or test-send one of their own templates.
 *
 * Shared by the e-mail test send and the Gebührenbescheid PDF preview so both show the same household.
 */
final class PreviewFixtureFactory
{
    public function create(): PreviewFixture
    {
        $eltern = new Stammdaten();
        $eltern->setVorname('Max');
        $eltern->setName('Mustermann');
        $eltern->setEmail('test@example.com');
        $eltern->setStrasse('Musterstraße 1');
        $eltern->setStadt('Musterstadt');
        // Stammdaten::setPlz() declares ?string; the inline fixtures this replaces passed an int and only got
        // away with it because that file did not declare strict types.
        $eltern->setPlz('12345');
        $eltern->setPhoneNumber('0123456789');
        $eltern->setGdpr(true);
        $eltern->setUid('UID123456');
        $eltern->setSecCode('testcode');
        $eltern->setCreatedAt(new \DateTime());
        // The Gebührenbescheid prints a Bankverbindung block, which would otherwise preview as empty cells.
        $eltern->setSepaInfo(true);
        $eltern->setIban('DE02120300000000202051');
        $eltern->setBic('BYLADEM1001');
        $eltern->setKontoinhaber('Max Mustermann');

        $kind = new Kind();
        $kind->setVorname('Lisa');
        $kind->setNachname('Mustermann');
        $kind->setGeburtstag(new \DateTime('2015-06-01'));
        $kind->setVegetarisch(true);
        $kind->setAusfluege(true);
        $kind->setEltern($eltern);

        $organisation = new Organisation();
        $organisation->setName('Musterträger e.V.');
        // The PDF footer (App\Service\pdfFooter) prints the address, contact and bank lines, so a name-only
        // organisation would render an almost empty footer in the preview.
        $organisation->setAdresse('Trägerweg 2');
        $organisation->setPlz('12345');
        $organisation->setOrt('Musterstadt');
        $organisation->setTelefon('0123456789');
        $organisation->setEmail('traeger@example.com');
        $organisation->setIban('DE02120300000000202051');
        $organisation->setBic('BYLADEM1001');
        $organisation->setBankName('Musterbank');

        $kind->addBeworben($this->block(7, 15, 9, 15));
        $kind->addZeitblock($this->block(10, 15, 12, 15));
        $kind->addWarteliste($this->block(10, 15, 12, 15));

        $eltern->addKind($kind);

        return new PreviewFixture($eltern, $kind, $organisation);
    }

    /**
     * Stub figures for the PDF preview.
     *
     * Deliberately not produced by FeeSummaryBuilder: the real calculation resolves the versioned household
     * through the database, which cannot work for fixtures that were never persisted. The total is set lower
     * than the line sum so a template's Ermäßigung branch is visible in the preview.
     */
    public function createStubFeeSummary(): FeeSummary
    {
        $von = new \DateTimeImmutable('first day of september this year');
        $bis = $von->modify('+10 months')->modify('last day of this month');
        $monate = 11;

        $faelligkeiten = [];
        for ($i = 0; $i < $monate; ++$i) {
            $faelligkeiten[] = $von->modify(sprintf('+%d months', $i))->setTime(0, 0);
        }

        return new FeeSummary(
            lines: [
                new FeeLine(0, 'Montag', '07:15', '09:15', 'Frühbetreuung', 'Musterschule', 21.00),
                new FeeLine(2, 'Mittwoch', '12:15', '14:00', 'Mittagsbetreuung', 'Musterschule', 21.00),
                new FeeLine(3, 'Donnerstag', '14:00', '17:00', 'Nachmittagsbetreuung', 'Musterschule', 42.00),
            ],
            gesamt: 75.60,
            summeDerPositionen: 84.00,
            einkommensklasse: 'Beispiel-Einkommensklasse 2',
            stichtag: new \DateTimeImmutable(),
            kundennummer: 'MUSTER-0001',
            angebote: [
                new FeeAngebot('Frühbetreuung', 1, 21.00, 21.00 * $monate),
                new FeeAngebot('Mittagsbetreuung', 1, 21.00, 21.00 * $monate),
                new FeeAngebot('Nachmittagsbetreuung', 1, 42.00, 42.00 * $monate),
            ],
            monate: $monate,
            faelligkeiten: $faelligkeiten,
            geschwisterAnzahl: 2,
            zeitraumVon: $von,
            zeitraumBis: $bis,
            schuljahr: $von->format('Y') . '/' . $bis->format('Y'),
        );
    }

    /**
     * Stub figures for the holiday-programme PDF preview.
     *
     * Hand-built rather than derived from the fixture entities, so the preview never depends on persisted
     * Ferienblock bookings.
     */
    public function createStubFerienFeeSummary(): FerienFeeSummary
    {
        $sommer = new \DateTimeImmutable('first day of august this year');

        $lines = [
            new FerienFeeLine('Lisa Mustermann', 'Zirkuswoche', $sommer, $sommer->modify('+4 days'), 'Musterhalle', 85.00),
            new FerienFeeLine('Lisa Mustermann', 'Waldabenteuer', $sommer->modify('+7 days'), $sommer->modify('+11 days'), 'Waldheim', 65.00),
            new FerienFeeLine('Tim Mustermann', 'Zirkuswoche', $sommer, $sommer->modify('+4 days'), 'Musterhalle', 85.00),
        ];

        return new FerienFeeSummary(
            lines: $lines,
            gesamt: 235.00,
            stichtag: new \DateTimeImmutable(),
            kundennummer: 'MUSTER-0001',
        );
    }

    private function block(int $vonHour, int $vonMinute, int $bisHour, int $bisMinute): Zeitblock
    {
        $block = new Zeitblock();
        $block->setVon((new \DateTimeImmutable())->setTime($vonHour, $vonMinute));
        $block->setBis((new \DateTimeImmutable())->setTime($bisHour, $bisMinute));
        $block->setWochentag(1);
        $block->setGanztag(1);
        $block->setPreise([1, 2, 3, 4]);

        return $block;
    }
}
