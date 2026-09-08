<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Kind;
use App\Repository\KindRepository;
use App\Service\ElternService;
use App\Service\Gebuehrenbescheid\FeeSummaryBuilder;
use App\Service\Gebuehrenbescheid\PrintGebuehrenbescheidService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Downloads the same fee notice that is attached to a registration confirmation.
 */
final class GebuehrenbescheidDownloadController extends AbstractController
{
    public function __construct(
        private readonly KindRepository $kindRepository,
        private readonly ElternService $elternService,
        private readonly FeeSummaryBuilder $feeSummaryBuilder,
        private readonly PrintGebuehrenbescheidService $printGebuehrenbescheidService,
    ) {
    }

    #[Route(
        path: '/org_child/show/detail/gebuehrenbescheid/{kindId}',
        name: 'child_detail_gebuehrenbescheid_download',
        requirements: ['kindId' => '\\d+'],
        methods: ['GET'],
    )]
    public function __invoke(int $kindId, Request $request): Response
    {
        $kind = $this->kindRepository->find($kindId);
        if (!$kind instanceof Kind) {
            throw $this->createNotFoundException('Kind nicht gefunden.');
        }

        $organisation = $kind->getSchule()?->getOrganisation();
        if ($organisation === null || $organisation !== $this->getUser()?->getOrganisation()) {
            throw $this->createAccessDeniedException('Kind gehört nicht zum eigenen Träger.');
        }

        $stadt = $kind->getSchule()?->getStadt();
        if ($stadt === null || !$stadt->getSettingsSkibSendGebuehrenbescheid()) {
            throw $this->createNotFoundException('Der Gebührenbescheid ist für diese Stadt nicht aktiviert.');
        }

        $date = $this->resolveDate($request->query->getString('date'));
        $eltern = $this->elternService->getElternForSpecificTimeAndKind($kind, $date);
        $locale = $eltern->getLanguage() ?: (string) $this->getParameter('kernel.default_locale');
        $fileName = sprintf('Gebuehrenbescheid_%s_%s', $kind->getVorname(), $kind->getNachname());

        $response = new Response(
            $this->printGebuehrenbescheidService->render(
                $stadt,
                $kind,
                $eltern,
                $organisation,
                $this->feeSummaryBuilder->build($kind),
                $locale,
                $fileName,
            ),
            Response::HTTP_OK,
            ['Content-Type' => 'application/pdf'],
        );
        $response->headers->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $fileName . '.pdf',
            'gebuehrenbescheid.pdf',
        );

        return $response;
    }

    private function resolveDate(string $date): \DateTime
    {
        if ($date !== '') {
            $parsedDate = \DateTime::createFromFormat('!d.m.Y', $date);
            if ($parsedDate !== false) {
                return $parsedDate;
            }
        }

        return new \DateTime();
    }
}
