<?php

namespace App\Controller;

use App\Entity\Rechnung;
use App\Entity\Sepa;
use App\Service\InfomaExportService;
use App\Service\PrintRechnungService;
use App\Service\SepaCreateService;
use App\Service\SepaExcel;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

final class SepaDetailController extends AbstractController
{
    public function __construct(private readonly ManagerRegistry $managerRegistry)
    {
    }

    #[Route('/org_accounting/sepa/detail', name: 'accounting_sepa_detail')]
    public function index(Request $request, SepaCreateService $sepaCreateService): Response
    {
        set_time_limit(600);
        $sepa = $this->findSepaForCurrentOrganisation($request->query->getInt('id'));
        $simulationDate = (clone $sepa->getVon())->modify('first day of next month');

        return $this->render('sepa_detail/detail.html.twig', [
            'sepa' => $sepa,
            'diffs' => $sepaCreateService->diffToThisMonth($sepa, $simulationDate),
            'simDate' => $simulationDate,
        ]);
    }

    #[Route('/org_accounting/print/detail', name: 'accounting_sepa_print')]
    public function print(Request $request, PrintRechnungService $printRechnungService): mixed
    {
        $invoice = $this->findInvoiceForCurrentOrganisation($request->query->getInt('id'));
        $fileName = $invoice->getRechnungsnummer() ?: 'Rechnung-'.$invoice->getId();

        return $printRechnungService->printRechnung(
            $fileName,
            $invoice->getSepa()->getOrganisation(),
            $invoice,
            'D',
        );
    }

    #[Route('/org_accounting/print/sepaXML', name: 'accounting_sepa_printXML')]
    public function printXml(Request $request): Response
    {
        $sepa = $this->findSepaForCurrentOrganisation($request->query->getInt('id'));
        $response = new Response($sepa->getSepaXML());
        $response->headers->set('Content-Disposition', $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'SEPA-'.$sepa->getCreatedAt()->format('dmY_H_i_s').'.xml',
        ));

        return $response;
    }

    #[Route('/org_accounting/print/excel', name: 'accounting_sepa_printExcel')]
    public function printExcel(Request $request, SepaExcel $sepaExcel): Response
    {
        $sepa = $this->findSepaForCurrentOrganisation($request->query->getInt('sepa_id'));

        return $this->file(
            $sepaExcel->generateExcel($sepa),
            'SEPA_ID'.$sepa->getId().'.xlsx',
            ResponseHeaderBag::DISPOSITION_INLINE,
        );
    }

    #[Route('/org_accounting/print/infoma', name: 'accounting_sepa_print_infoma', methods: ['POST'])]
    public function printInfoma(Request $request, InfomaExportService $exportService): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ORG_INFOMA_EXPORT');
        $sepa = $this->findSepaForCurrentOrganisation($request->request->getInt('sepa_id'));
        if (!$this->isCsrfTokenValid('infoma-export-'.$sepa->getId(), (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Ungültiges CSRF-Token.');
        }

        $revenueAccount = trim((string) $request->request->get('account'));
        $bankAccount = trim((string) $request->request->get('counter_account'));
        $externalSystemId = trim((string) $request->request->get('external_system_id'));
        $paymentMethodCode = trim((string) $request->request->get('payment_method_code'));
        foreach ([$revenueAccount, $bankAccount, $externalSystemId, $paymentMethodCode] as $value) {
            if (!preg_match('/^[A-Za-z0-9.-]+$/', $value)) {
                throw new BadRequestHttpException('Bitte geben Sie gültige Infoma-Codes und Konten an.');
            }
        }

        try {
            $csv = $exportService->generate(
                $sepa,
                $revenueAccount,
                $bankAccount,
                $externalSystemId,
                $paymentMethodCode,
            );
        } catch (\InvalidArgumentException $exception) {
            throw new BadRequestHttpException($exception->getMessage(), $exception);
        }

        $response = new Response($csv, Response::HTTP_OK, ['Content-Type' => 'text/csv; charset=UTF-8']);
        $response->headers->set('Content-Disposition', $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'INFOMA_SEPA_ID'.$sepa->getId().'.csv',
        ));

        return $response;
    }

    private function findSepaForCurrentOrganisation(int $id): Sepa
    {
        $sepa = $this->managerRegistry->getRepository(Sepa::class)->find($id);
        if (!$sepa || $sepa->getOrganisation() !== $this->getUser()?->getOrganisation()) {
            throw $this->createNotFoundException();
        }

        return $sepa;
    }

    private function findInvoiceForCurrentOrganisation(int $id): Rechnung
    {
        $invoice = $this->managerRegistry->getRepository(Rechnung::class)->find($id);
        if (!$invoice || $invoice->getSepa()?->getOrganisation() !== $this->getUser()?->getOrganisation()) {
            throw $this->createNotFoundException();
        }

        return $invoice;
    }
}
