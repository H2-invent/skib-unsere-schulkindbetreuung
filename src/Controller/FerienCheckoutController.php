<?php

namespace App\Controller;

use App\Entity\Payment;
use App\Entity\PaymentBraintree;
use App\Entity\PaymentSepa;
use App\Entity\Stadt;
use App\Form\Type\PaymentType;
use App\Service\CheckoutBraintreeService;
use App\Service\CheckoutPaymentService;
use App\Service\CheckoutSepaService;
use App\Service\StamdatenFromCookie;
use Braintree\Gateway;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class FerienCheckoutController extends AbstractController
{
    /**
     * @Route("/{slug}/ferien/bezahlung",name="ferien_bezahlung",methods={"Get","POST"})
     * @ParamConverter("stadt", options={"mapping"={"slug"="slug"}})
     */
    public function paymentAction(Request $request, TranslatorInterface $translator, ValidatorInterface $validator, Stadt $stadt, StamdatenFromCookie $stamdatenFromCookie, CheckoutSepaService $checkoutSepaService)
    {
        //Include Parents in this route
        if ($stamdatenFromCookie->getStammdatenFromCookie($request, FerienController::BEZEICHNERCOOKIE)) {
            $adresse = $stamdatenFromCookie->getStammdatenFromCookie($request, FerienController::BEZEICHNERCOOKIE);
        }
        $payment = new PaymentSepa();

        if (!$adresse->getPaymentFerien()->isEmpty() && $adresse->getPaymentFerien()->toArray()[0]->getSepa()) {
            $payment = $adresse->getPaymentFerien()->get(0)->getSepa();
        }
        $form = $this->createForm(PaymentType::class, $payment);
        $form->handleRequest($request);
        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $payment = $form->getData();
            $errors = $validator->validate($payment);
            if (sizeof($errors) == 0) {
                if ($res = $checkoutSepaService->generateSepaPayment($adresse, $payment, $request->getClientIp())) {
                    return $this->redirectToRoute('ferien_zusammenfassung', array('slug' => $stadt->getSlug()));
                } else {
                    return $this->redirectToRoute('ferien_bezahlung', array('slug' => $stadt->getSlug(), 'snack' => $translator->trans('Fehler. Bitte versuchen Sie es erneut.')));
                }
            }
        }
        $gateway = new Gateway([
            'environment' => 'sandbox',
            'merchantId' => '65xmpcc6hh6khg5d',
            'publicKey' => 'wzkfsj9n2kbyytfp',
            'privateKey' => 'a153a39aaef70466e97773a120b95f91',
        ]);
        $clientToken = $gateway->clientToken()->generate();
        return $this->render('ferien_checkout/bezahlung.html.twig', array('stadt' => $stadt, 'token' => $clientToken, 'form' => $form->createView()));
    }

    /**
     * @Route("/{slug}/ferien/epayment/prepare",name="ferien_braintree_start",methods={"Get"})
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
     * @Route("/{slug}/ferien/epayment/pay",name="ferien_braintree_pay",methods={"GET","POST"})
     * @ParamConverter("stadt", options={"mapping"={"slug"="slug"}})
     */
    public function paymentpayAction(Stadt $stadt, TranslatorInterface $translator, CheckoutPaymentService $checkoutPaymentService, Request $request, StamdatenFromCookie $stamdatenFromCookie)
    {
        if ($stamdatenFromCookie->getStammdatenFromCookie($request, FerienController::BEZEICHNERCOOKIE)) {
            $adresse = $stamdatenFromCookie->getStammdatenFromCookie($request, FerienController::BEZEICHNERCOOKIE);
        }
        $openPayment = $checkoutPaymentService->getPaymentWithEmptyNonce($adresse)->toArray();
        if (sizeof($openPayment) > 0) {
            return $this->render('ferien_checkout/braintreePayment.html.twig', array(
                'payment' => $openPayment[0],
                'open' => $openPayment,
                'paymentAll' => $adresse->getPaymentFerien(),
                'stadt' => $stadt));
        }
        return $this->redirectToRoute('ferien_zusammenfassung', array('slug' => $stadt->getSlug()));
    }

    /**
     * @Route("/ferien/epayment/recieveNonce",name="ferien_braintree_nonce",methods={"POST"})
     */
    public function paymentrecieveNonceAction(TranslatorInterface $translator, CheckoutPaymentService $checkoutPaymentService, Request $request, StamdatenFromCookie $stamdatenFromCookie)
    {
        if ($stamdatenFromCookie->getStammdatenFromCookie($request, FerienController::BEZEICHNERCOOKIE)) {
            $adresse = $stamdatenFromCookie->getStammdatenFromCookie($request, FerienController::BEZEICHNERCOOKIE);
        }
        $braintree = $this->getDoctrine()->getRepository(PaymentBraintree::class)->findOneBy(array('token' => $request->get('token')));
        $braintree->setNonce($request->get('nonce'));
        $em = $this->getDoctrine()->getManager();
        $em->persist($braintree);
        $em->flush();
        return new JsonResponse(array('error' => 0));
    }
}
