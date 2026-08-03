<?php

declare(strict_types=1);

namespace App\Service\Gebuehrenbescheid;

use App\Entity\Organisation;
use App\Entity\Stadt;
use App\Service\PrintService;
use Qipsius\TCPDFBundle\Controller\TCPDFController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

/**
 * Shared plumbing for the Gebührenbescheid PDFs: locale resolution, template lookup and TCPDF output.
 *
 * The Schulkindbetreuung and the holiday programme each have their own template field, wrapper template and
 * context, but the surrounding mechanics are identical.
 */
final class GebuehrenbescheidPdfRenderer
{
    /**
     * PrintService::preparePDF() draws the logo at y = 15 with a height of up to 30 mm, so the body has to
     * start below that. Without a logo there is nothing to clear and the body starts just under the top margin.
     */
    private const TOP_WITH_LOGO = 50;
    private const TOP_WITHOUT_LOGO = 20;

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
     * Returns the city-authored template for $locale, or '' when none was authored.
     *
     * Doctrine-Behaviors' translate() only falls back to the default locale when there is no translation row
     * for the requested locale at all. Cities normally do have en/fr rows (StadtType seeds them), so an
     * unfilled French template would yield an empty string rather than the German one. Falling back here keeps
     * a parent with a French profile from silently receiving no fee notice.
     *
     * @param callable(object): ?string $read reads the wanted field off a StadtTranslation
     */
    public function resolveTemplate(Stadt $stadt, string $locale, callable $read): string
    {
        $source = $this->templateFor($stadt, $locale, $read);
        if ($source === '' && $locale !== $this->defaultLocale) {
            $source = $this->templateFor($stadt, $this->defaultLocale, $read);
        }

        return $source;
    }

    /**
     * @param array<string, mixed> $context
     *
     * @return string raw PDF bytes
     */
    public function render(
        string $wrapperTemplate,
        array $context,
        ?Organisation $organisation,
        string $locale,
        string $fileName,
    ): string {
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

            $html = $this->twig->render($wrapperTemplate, $context);
            $top = $organisation?->getImage() ? self::TOP_WITH_LOGO : self::TOP_WITHOUT_LOGO;
            $pdf->writeHTMLCell(0, 0, 20, $top, $html, 0, 1, 0, true, '', true);

            return $pdf->Output($fileName . '.pdf', 'S');
        } finally {
            $this->translator->setLocale($previousLocale);
        }
    }

    /**
     * strip_tags() matters: the WYSIWYG editor leaves markup such as <p><br></p> behind in a field the admin
     * has visually emptied, which is not a template worth rendering.
     *
     * @param callable(object): ?string $read
     */
    private function templateFor(Stadt $stadt, string $locale, callable $read): string
    {
        $source = (string) $read($stadt->translate($locale));

        return trim(strip_tags($source)) === '' ? '' : $source;
    }
}
