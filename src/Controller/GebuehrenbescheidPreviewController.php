<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\StadtRepository;
use App\Service\Gebuehrenbescheid\PrintFerienGebuehrenbescheidService;
use App\Service\Gebuehrenbescheid\PrintGebuehrenbescheidService;
use App\Service\TemplatePreview\PreviewFixtureFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Renders the city's Gebührenbescheid template against sample data so an admin can see the actual PDF.
 */
final class GebuehrenbescheidPreviewController extends AbstractController
{
    private const SUPPORTED_LOCALES = ['de', 'en', 'fr'];

    public function __construct(
        private readonly StadtRepository $stadtRepository,
        private readonly PreviewFixtureFactory $previewFixtureFactory,
        private readonly PrintGebuehrenbescheidService $printGebuehrenbescheidService,
        private readonly PrintFerienGebuehrenbescheidService $printFerienGebuehrenbescheidService,
    ) {
    }

    #[Route(
        path: '/city_edit/stadtverwaltung/gebuehrenbescheid/vorschau',
        name: 'app_gebuehrenbescheid_pdf_preview',
        methods: ['GET'],
    )]
    public function __invoke(Request $request): Response
    {
        // Hierarchy-aware, matching the is_granted() check that decides whether the form field is rendered.
        $this->denyAccessUnlessGranted('ROLE_CITY_FEE_NOTICE_EDITOR');

        $stadt = $this->stadtRepository->find($request->query->get('stadt'));
        if ($stadt === null) {
            throw $this->createNotFoundException('Stadt not found');
        }

        // The /city_edit/ prefix only proves a role, not that this city belongs to the user.
        if (!$this->getUser()->hasRole('ROLE_ADMIN') && $stadt !== $this->getUser()->getStadt()) {
            throw $this->createAccessDeniedException('Wrong City');
        }

        $locale = (string) $request->query->get('locale', $request->getLocale());
        if (!in_array($locale, self::SUPPORTED_LOCALES, true)) {
            $locale = 'de';
        }

        $fixture = $this->previewFixtureFactory->create();
        $ferien = $request->query->get('variant') === 'ferien';

        try {
            $pdf = $ferien
                ? $this->printFerienGebuehrenbescheidService->render(
                    $stadt,
                    $fixture->eltern,
                    $fixture->organisation,
                    $this->previewFixtureFactory->createStubFerienFeeSummary(),
                    $locale,
                    'Gebuehrenbescheid-Ferienprogramm-Vorschau',
                )
                : $this->printGebuehrenbescheidService->render(
                    $stadt,
                    $fixture->kind,
                    $fixture->eltern,
                    $fixture->organisation,
                    $this->previewFixtureFactory->createStubFeeSummary(),
                    $locale,
                    'Gebuehrenbescheid-Vorschau',
                );
        } catch (\Throwable $exception) {
            // A template error is the admin's own typo, so show it to them instead of an error page.
            return new Response(
                'Fehler im Template: ' . $exception->getMessage(),
                Response::HTTP_OK,
                ['Content-Type' => 'text/plain; charset=utf-8'],
            );
        }

        return new Response($pdf, Response::HTTP_OK, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="gebuehrenbescheid-vorschau.pdf"',
        ]);
    }
}
