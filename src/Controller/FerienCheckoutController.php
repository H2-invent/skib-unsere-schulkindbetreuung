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
                if ($checkoutSepaService->generateSepaPayment($adresse, $payment, $request->getClientIp())) {
                    return $this->redirectToRoute('ferien_zusammenfassung', array('slug' => $stadt->getSlug()));
                } else {
                    return $this->redirectToRoute('ferien_bezahlung', array('slug' => $stadt->getSlug(), 'snack' => $translator->trans('Fehler. Bitte versuchen Sie es erneut.')));
                }
            }
        }
        return $this->render('ferien_checkout/bezahlung.html.twig', array('stadt' => $stadt,  'form' => $form->createView()));
    }


}
