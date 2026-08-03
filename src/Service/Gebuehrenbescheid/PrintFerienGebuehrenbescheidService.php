<?php

declare(strict_types=1);

namespace App\Service\Gebuehrenbescheid;

use App\Dto\Gebuehrenbescheid\FerienFeeSummary;
use App\Entity\Organisation;
use App\Entity\Stadt;
use App\Entity\StadtTranslation;
use App\Entity\Stammdaten;

/**
 * Renders the holiday-programme Gebührenbescheid from the Twig source a city admin authored in the backend.
 *
 * Issued per household rather than per child, because the holiday programme is booked and paid for as one
 * basket per household.
 */
final class PrintFerienGebuehrenbescheidService
{
    private const TEMPLATE = 'pdf/gebuehrenbescheidFerien.html.twig';

    public function __construct(
        private readonly GebuehrenbescheidPdfRenderer $renderer,
    ) {
    }

    /**
     * Whether the city itself authored a template for this locale; see
     * {@see PrintGebuehrenbescheidService::hasTemplate()} for why this does not gate rendering.
     */
    public function hasTemplate(Stadt $stadt, string $locale): bool
    {
        return $this->resolveTemplate($stadt, $locale) !== '';
    }

    /**
     * @return string raw PDF bytes
     */
    public function render(
        Stadt $stadt,
        Stammdaten $eltern,
        ?Organisation $organisation,
        FerienFeeSummary $gebuehren,
        string $locale,
        ?string $fileName = null,
    ): string {
        $fileName ??= 'Gebuehrenbescheid-Ferienprogramm';

        return $this->renderer->render(
            self::TEMPLATE,
            [
                'template' => $this->resolveTemplate($stadt, $locale),
                'stadt' => $stadt,
                'eltern' => $eltern,
                'stammdaten' => $eltern,
                'organisation' => $organisation,
                'gebuehren' => $gebuehren,
                'datum' => new \DateTimeImmutable(),
                'locale' => $locale,
            ],
            $organisation,
            $locale,
            $fileName,
        );
    }

    private function resolveTemplate(Stadt $stadt, string $locale): string
    {
        return $this->renderer->resolveTemplate(
            $stadt,
            $locale,
            static fn (StadtTranslation $translation): ?string => $translation->getPdftemplateGebuehrenbescheidFerien(),
        );
    }
}
