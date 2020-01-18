<?php

namespace App\Controller;

use App\Entity\Payment;
use App\Entity\PaymentBraintree;
use App\Entity\PaymentStripe;
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

class PaymentStripeController extends AbstractController
{
    public function __construct()
    {

    }

    /**
     * @Route("/{slug}/ferien/stripe/prepare",name="ferien_stripe_start",methods={"Get"})
     * @ParamConverter("stadt", options={"mapping"={"slug"="slug"}})
     */
    public function paymentPrepareAction(CheckoutPaymentService $checkoutPaymentService, Stadt $stadt, TranslatorInterface $translator, CheckoutBraintreeService $checkoutBraintreeService, Request $request, StamdatenFromCookie $stamdatenFromCookie)
    {
        if ($stamdatenFromCookie->getStammdatenFromCookie($request, FerienController::BEZEICHNERCOOKIE)) {
            $adresse = $stamdatenFromCookie->getStammdatenFromCookie($request, FerienController::BEZEICHNERCOOKIE);
        }
        $payment = $this->getDoctrine()->getRepository(Payment::class)->findOneBy(array('uid'=>$request->get('id')));

        return $this->render('payment_stripe/index.html.twig', array('payment' => $payment, 'stadt' => $stadt,'org'=>$payment->getOrganisation()));
    }

    /**
     * @Route("/{slug}/ferien/stripe/recieveToken",name="ferien_stripe_token", methods={"POST"})
     * @ParamConverter("stadt", options={"mapping"={"slug"="slug"}})
     */
    public function paymentrecieveNonceAction( Stadt $stadt, Request $request)
    {
        $payment = $this->getDoctrine()->getRepository(Payment::class)->findOneBy(array('uid' => $request->get('paymentId')));
        $stripe = new PaymentStripe();
        $stripe->setChargeId($request->get('stripeToken'));
        $payment->setPaymentStripe($stripe);
        $stripe->setStatus(false);
        $payment->setFinished(true);
        $payment->setArtString('Credit Card');
        $em = $this->getDoctrine()->getManager();
        $em->persist($stripe);
        $em->persist($payment);
        $em->flush();
        return $this->redirectToRoute('ferien_zusammenfassung',array('slug'=>$stadt->getSlug()));
    }
}
