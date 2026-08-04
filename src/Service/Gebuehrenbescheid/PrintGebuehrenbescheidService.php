<?php

declare(strict_types=1);

namespace App\Service\Gebuehrenbescheid;

use App\Dto\Gebuehrenbescheid\FeeSummary;
use App\Entity\Kind;
use App\Entity\Organisation;
use App\Entity\Stadt;
use App\Entity\StadtTranslation;
use App\Entity\Stammdaten;

/**
 * Renders the Schulkindbetreuung Gebührenbescheid from the Twig source a city admin authored in the backend.
 *
 * Unlike the other Print* services the document body does not come from a static template: the file template
 * templates/pdf/gebuehrenbescheid.html.twig is only a wrapper that runs the DB-stored source through
 * template_from_string, falling back to a built-in default layout when the city authored none.
 */
final class PrintGebuehrenbescheidService
{
    private const TEMPLATE = 'pdf/gebuehrenbescheid.html.twig';

    public function __construct(
        private readonly GebuehrenbescheidPdfRenderer $renderer,
    ) {
    }

    /**
     * Whether the city itself authored a template for this locale, the default locale counting as a fallback.
     *
     * A false result does not stop {@see self::render()}: it then produces the built-in default layout. This is
     * informational only, e.g. for logging that a city is relying on the default.
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
        Kind $kind,
        Stammdaten $eltern,
        ?Organisation $organisation,
        FeeSummary $gebuehren,
        string $locale,
        ?string $fileName = null,
    ): string {
        $fileName ??= 'Gebuehrenbescheid';

        return $this->renderer->render(
            self::TEMPLATE,
            [
                'template' => $this->resolveTemplate($stadt, $locale),
                'stadt' => $stadt,
                'kind' => $kind,
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
            static fn (StadtTranslation $translation): ?string => $translation->getPdftemplateGebuehrenbescheid(),
        );
    }
}
