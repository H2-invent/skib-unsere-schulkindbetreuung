<?php

declare(strict_types=1);

namespace App\Service\Gebuehrenbescheid;

use App\Dto\Gebuehrenbescheid\FeeSummary;
use App\Entity\Kind;
use App\Entity\Organisation;
use App\Entity\Stadt;
use App\Entity\Stammdaten;
use App\Service\PrintService;
use Qipsius\TCPDFBundle\Controller\TCPDFController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

/**
 * Renders the Gebührenbescheid PDF from the Twig source a city admin authored in the backend.
 *
 * Unlike the other Print* services the document body does not come from a static template: the file template
 * templates/pdf/gebuehrenbescheid.html.twig is only a wrapper that runs the DB-stored source through
 * template_from_string.
 */
final class PrintGebuehrenbescheidService
{
    private const TEMPLATE = 'pdf/gebuehrenbescheid.html.twig';

    public function __construct(
        private readonly TCPDFController $tcpdf,
        private readonly PrintService $printService,
        private readonly Environment $twig,
        private readonly TranslatorInterface $translator,
        #[Autowire('%kernel.default_locale%')]
        private readonly string $defaultLocale,
    ) {
    }

    /**
     * Whether a template is available for this locale, the default locale counting as a fallback.
     */
    public function hasTemplate(Stadt $stadt, string $locale): bool
    {
        return $this->resolveTemplate($stadt, $locale) !== '';
    }

    /**
     * Returns the template to use for $locale, or '' when the city has authored none.
     *
     * Doctrine-Behaviors' translate() only falls back to the default locale when there is no translation row
     * for the requested locale at all. Cities normally do have en/fr rows (StadtType seeds them), so an
     * unfilled French template would yield an empty string rather than the German one. Falling back here keeps
     * a parent with a French profile from silently receiving no fee notice.
     */
    private function resolveTemplate(Stadt $stadt, string $locale): string
    {
        $source = $this->templateFor($stadt, $locale);
        if ($source === '' && $locale !== $this->defaultLocale) {
            $source = $this->templateFor($stadt, $this->defaultLocale);
        }

        return $source;
    }

    /**
     * strip_tags() matters: the WYSIWYG editor leaves markup such as <p><br></p> behind in a field the admin
     * has visually emptied, which is not a template worth rendering.
     */
    private function templateFor(Stadt $stadt, string $locale): string
    {
        $source = (string) $stadt->translate($locale)->getPdftemplateGebuehrenbescheid();

        return trim(strip_tags($source)) === '' ? '' : $source;
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
        // Explicit locale rather than the ambient one: this is called from a mail flow that switches the
        // translator locale around the body render, and translate() must not depend on that ordering.
        $source = $this->resolveTemplate($stadt, $locale);

        $previousLocale = $this->translator->getLocale();
        $this->translator->setLocale($locale);

        try {
            $pdf = $this->tcpdf->create();
            if ($organisation !== null) {
                $pdf->setOrganisation($organisation);
            }

            // Passing null for the Stadt is deliberate: preparePDF() lets the city logo win over the
            // organisation's, and the fee notice carries the provider's branding like every other PDF here.
            $pdf = $this->printService->preparePDF($pdf, $fileName, '', $fileName, null, $organisation);

            $html = $this->twig->render(self::TEMPLATE, [
                'template' => $source,
                'stadt' => $stadt,
                'kind' => $kind,
                'eltern' => $eltern,
                'stammdaten' => $eltern,
                'organisation' => $organisation,
                'gebuehren' => $gebuehren,
                'datum' => new \DateTimeImmutable(),
                'locale' => $locale,
            ]);

            $pdf->writeHTMLCell(0, 0, 20, 50, $html, 0, 1, 0, true, '', true);

            return $pdf->Output($fileName . '.pdf', 'S');
        } finally {
            $this->translator->setLocale($previousLocale);
        }
    }
}
