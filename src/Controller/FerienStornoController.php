<?php

namespace App\Controller;

use App\Entity\Ferienblock;
use App\Entity\Kind;
use App\Entity\KindFerienblock;
use App\Entity\PaymentRefund;
use App\Entity\Stadt;
use App\Entity\Stammdaten;
use App\Service\CheckoutPaymentService;
use App\Service\FerienStornoService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use function Doctrine\ORM\QueryBuilder;

class FerienStornoController extends AbstractController
{
    /**
     * @Route("/{slug}/ferien/storno", name="ferien_storno")
     */
    public function index($slug, Request $request)
    {
        $stadt = $this->getDoctrine()->getRepository(Stadt::class)->findOneBy(array('slug' => $slug));
        $stammdaten = $this->getDoctrine()->getRepository(Stammdaten::class)->findOneBy(array('uid' => $request->get('parent_id')));
        //$kindFerienblock = $this->getDoctrine()->getRepository(KindFerienblock::class)->findOneBy(array('kind' => $request->get('kind_id')));

        $kind = $stammdaten ->getKinds();

        return $this->render('ferien_storno/index.html.twig', [
            'stadt' => $stadt,
            'kind' => $kind,
            'eltern' => $stammdaten,
        ]);
    }


    /**
     * @Route("/ferien/storno/mark", name="ferien_storno_mark", methods={"PATCH"})
     */
    public function markAction(TranslatorInterface $translator, Request $request,FerienStornoService $ferienStornoService)
    {
        $stammdaten = $this->getDoctrine()->getRepository(Stammdaten::class)->findOneBy(array('uid' => $request->get('parent_id')));
        $kindFerienblock = $this->getDoctrine()->getRepository(KindFerienblock::class)->findOneBy(array('id' => $request->get('block_id')));
        return new JsonResponse($ferienStornoService->toggleBlock($kindFerienblock,$stammdaten));
    }

    /**
     * @Route("/{slug}/ferien/storno/abschluss", name="ferien_storno_abschluss", methods={"GET"})
     */
    public function stornoAbschluss(LoggerInterface $logger, $slug,TranslatorInterface $translator, Request $request,FerienStornoService $ferienStornoService)
    {
        $stadt = $this->getDoctrine()->getRepository(Stadt::class)->findOneBy(array('slug' => $slug));

        $stammdaten = $this->getDoctrine()->getRepository(Stammdaten::class)->findOneBy(array('uid' => $request->get('parent_id')));

         $logger->info('Start Storn for '.$stammdaten->getId());
         $ferienStornoService->stornoAbschluss($stammdaten,$request->getClientIp());
        $res = $this->render('ferien/abschluss.html.twig', array('stadt' => $stadt));

        return $res;

    }
    /**
     * @Route("/org_ferien/storno/payBack", name="ferien_storno_payPack", methods={"PATCH"})
     */
    public function payBackAction(CheckoutPaymentService $checkoutPaymentService, TranslatorInterface $translator, Request $request,FerienStornoService $ferienStornoService)
    {
        $refund = $this->getDoctrine()->getRepository(PaymentRefund::class)->find($request->get('id'));

        return new JsonResponse(array('error'=>$checkoutPaymentService->makeRefundPAyment($refund),'redirect'=>$this->generateUrl('ferien_management_order_detail',array('org_id'=>$refund->getPayment()->getOrganisation()->getId(),'id'=>$refund->getPayment()->getStammdaten()->getId()))));
    }
}
