<?php

namespace App\Controller;

use App\Entity\KindFerienblock;
use App\Entity\PaymentRefund;
use App\Entity\Stadt;
use App\Entity\Stammdaten;
use App\Service\CheckoutPaymentService;
use App\Service\FerienStornoService;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class FerienStornoController extends AbstractController
{
    public function __construct(
        private ManagerRegistry $managerRegistry,
    ) {
    }

    #[Route(path: '/{slug}/ferien/storno', name: 'ferien_storno')]
    public function index($slug, Request $request)
    {
        $stadt = $this->managerRegistry->getRepository(Stadt::class)->findOneBy(['slug' => $slug]);
        $stammdaten = $this->managerRegistry->getRepository(Stammdaten::class)->findOneBy(['uid' => $request->get('parent_id')]);

        $kind = $stammdaten->getKinds();

        return $this->render('ferien_storno/index.html.twig', [
            'stadt' => $stadt,
            'kind' => $kind,
            'eltern' => $stammdaten,
        ]);
    }

    #[Route(path: '/ferien/storno/mark', name: 'ferien_storno_mark', methods: ['PATCH'])]
    public function markAction(TranslatorInterface $translator, Request $request, FerienStornoService $ferienStornoService)
    {
        $stammdaten = $this->managerRegistry->getRepository(Stammdaten::class)->findOneBy(['uid' => $request->get('parent_id')]);
        $kindFerienblock = $this->managerRegistry->getRepository(KindFerienblock::class)->findOneBy(['id' => $request->get('block_id')]);

        return new JsonResponse($ferienStornoService->toggleBlock($kindFerienblock, $stammdaten));
    }

    #[Route(path: '/{slug}/ferien/storno/abschluss', name: 'ferien_storno_abschluss', methods: ['GET'])]
    public function stornoAbschluss(LoggerInterface $logger, $slug, TranslatorInterface $translator, Request $request, FerienStornoService $ferienStornoService)
    {
        $stadt = $this->managerRegistry->getRepository(Stadt::class)->findOneBy(['slug' => $slug]);

        $stammdaten = $this->managerRegistry->getRepository(Stammdaten::class)->findOneBy(['uid' => $request->get('parent_id')]);

        $logger->info('Start Storno for ' . $stammdaten->getId());
        $ferienStornoService->stornoAbschluss($stammdaten, $request->getClientIp());
        $res = $this->render('ferien/abschluss.html.twig', ['stadt' => $stadt]);

        return $res;
    }

    #[Route(path: '/org_ferien/storno/payBack', name: 'ferien_storno_payPack', methods: ['PATCH'])]
    public function payBackAction(CheckoutPaymentService $checkoutPaymentService, TranslatorInterface $translator, Request $request, FerienStornoService $ferienStornoService)
    {
        $refund = $this->managerRegistry->getRepository(PaymentRefund::class)->find($request->get('id'));

        return new JsonResponse(['error' => $checkoutPaymentService->makeRefundPAyment($refund), 'redirect' => $this->generateUrl('ferien_management_order_detail', ['org_id' => $refund->getPayment()->getOrganisation()->getId(), 'id' => $refund->getPayment()->getStammdaten()->getId()])]);
    }
}
