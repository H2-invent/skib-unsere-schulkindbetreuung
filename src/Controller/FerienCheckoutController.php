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
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class FerienCheckoutController extends AbstractController
{
    /**
     * @Route("/{slug}/ferien/payment/prepare",name="ferien_bezahlung_prepare",methods={"Get"})
     * @ParamConverter("stadt", options={"mapping"={"slug"="slug"}})
     */
    public function prepareAction(CheckoutPaymentService $checkoutPaymentService, Request $request, TranslatorInterface $translator, ValidatorInterface $validator, Stadt $stadt, StamdatenFromCookie $stamdatenFromCookie, CheckoutSepaService $checkoutSepaService)
    {
        if ($stamdatenFromCookie->getStammdatenFromCookie($request, FerienController::BEZEICHNERCOOKIE)) {
            $adresse = $stamdatenFromCookie->getStammdatenFromCookie($request, FerienController::BEZEICHNERCOOKIE);
        }
        $checkoutPaymentService->createPayment($adresse, $request->getClientIp());
        return $this->redirectToRoute('ferien_bezahlung', array('slug' => $stadt->getSlug()));
    }

    /**
     * @Route("/{slug}/ferien/bezahlung",name="ferien_bezahlung",methods={"Get","POST"})
     * @ParamConverter("stadt", options={"mapping"={"slug"="slug"}})
     */
    public function paymentAction(CheckoutPaymentService $checkoutPaymentService, Request $request, TranslatorInterface $translator, ValidatorInterface $validator, Stadt $stadt, StamdatenFromCookie $stamdatenFromCookie, CheckoutSepaService $checkoutSepaService)
    {

        if ($stamdatenFromCookie->getStammdatenFromCookie($request, FerienController::BEZEICHNERCOOKIE)) {
            $adresse = $stamdatenFromCookie->getStammdatenFromCookie($request, FerienController::BEZEICHNERCOOKIE);
        }
        $payment = $checkoutPaymentService->getNextPayment($adresse);
        if ($payment) {
            $paymentSepa = $checkoutPaymentService->findSepaInEltern($adresse, $payment->getOrganisation());
        } else {
            if($adresse->getFin() === true){
                return $this->redirectToRoute('ferien_abschluss',array('slug'=>$stadt->getSlug()));
            }
            return $this->redirectToRoute('ferien_zusammenfassung', array('slug' => $stadt->getSlug()));
        }

        $form = $this->createForm(PaymentType::class, $paymentSepa);
        $form->handleRequest($request);
        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $paymentSepa = new PaymentSepa();
            $paymentSepa = $form->getData();
            $errors = $validator->validate($payment);
            if (sizeof($errors) == 0) {
                $checkoutSepaService->generateSepaPayment($adresse, $paymentSepa, $request->getClientIp(), $payment);
                if ($checkoutPaymentService->getNextPayment($adresse) == null) {
                    return $this->redirectToRoute('ferien_zusammenfassung', array('slug' => $stadt->getSlug()));
                } else {
                    return $this->redirectToRoute('ferien_bezahlung', array('slug' => $stadt->getSlug(), 'snack' => $translator->trans('Fehler. Bitte versuchen Sie es erneut.')));
                }
            }
        }

        return $this->render('ferien_checkout/bezahlung.html.twig', array('payment' => $payment, 'stadt' => $stadt, 'form' => $form->createView()));
    }


}
