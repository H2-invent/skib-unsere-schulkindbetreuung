<?php

namespace App\Controller;

use App\Entity\PaymentBraintree;
use App\Entity\Stadt;
use App\Service\CheckoutBraintreeService;
use App\Service\CheckoutPaymentService;
use App\Service\StamdatenFromCookie;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class BrainTreeCheckoutController extends AbstractController
{
    /**
     * @Route("/{slug}/ferien/braintree/prepare",name="ferien_braintree_start",methods={"Get"})
     * @ParamConverter("stadt", options={"mapping"={"slug"="slug"}})
     */
    public function paymentPrepareAction(Stadt $stadt, TranslatorInterface $translator, CheckoutBraintreeService $checkoutBraintreeService, Request $request, StamdatenFromCookie $stamdatenFromCookie)
    {
        if ($stamdatenFromCookie->getStammdatenFromCookie($request, FerienController::BEZEICHNERCOOKIE)) {
            $adresse = $stamdatenFromCookie->getStammdatenFromCookie($request, FerienController::BEZEICHNERCOOKIE);
        }
        if ($checkoutBraintreeService->prepareBraintree($adresse, $request->getClientIp())) {
            return $this->redirectToRoute('ferien_braintree_pay', array('slug' => $stadt->getSlug()));
        }

        return $this->redirectToRoute('ferien_bezahlung', array('slug' => $stadt->getSlug(), 'snack' => $translator->trans('Fehler. Bitte versuchen Sie es erneut.')));


    }

    /**
     * @Route("/{slug}/ferien/braintree/pay",name="ferien_braintree_pay",methods={"GET","POST"})
     * @ParamConverter("stadt", options={"mapping"={"slug"="slug"}})
     */
    public function paymentpayAction(Stadt $stadt, TranslatorInterface $translator, CheckoutPaymentService $checkoutPaymentService, Request $request, StamdatenFromCookie $stamdatenFromCookie)
    {
        if ($stamdatenFromCookie->getStammdatenFromCookie($request, FerienController::BEZEICHNERCOOKIE)) {
            $adresse = $stamdatenFromCookie->getStammdatenFromCookie($request, FerienController::BEZEICHNERCOOKIE);
        }
        $openPayment = $checkoutPaymentService->getPaymentWithEmptyNonce($adresse)->toArray();
        if (sizeof($openPayment) > 0) {
            return $this->render('ferien_checkout/braintreePayment.html.twig', array('payment' => $openPayment[0], 'open' => $openPayment, 'paymentAll' => $adresse->getPaymentFerien(), 'stadt' => $stadt));
        }
        return $this->redirectToRoute('ferien_zusammenfassung', array('slug' => $stadt->getSlug()));
    }

    /**
     * @Route("/ferien/braintree/recieveNonce",name="ferien_braintree_nonce",methods={"POST"})
     */
    public function paymentrecieveNonceAction(TranslatorInterface $translator, CheckoutPaymentService $checkoutPaymentService, Request $request, StamdatenFromCookie $stamdatenFromCookie)
    {
        $braintree = $this->getDoctrine()->getRepository(PaymentBraintree::class)->findOneBy(array('token' => $request->get('token')));
        $braintree->setNonce($request->get('nonce'));
        $em = $this->getDoctrine()->getManager();
        $em->persist($braintree);
        $em->flush();
        return new JsonResponse(array('error' => 0));
    }
}
