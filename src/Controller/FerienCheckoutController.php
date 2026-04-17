<?php

namespace App\Controller;

use App\Entity\PaymentSepa;
use App\Entity\Stadt;
use App\Form\Type\PaymentType;
use App\Service\CheckoutPaymentService;
use App\Service\CheckoutSepaService;
use App\Service\StamdatenFromCookie;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class FerienCheckoutController extends AbstractController
{
    #[Route(path: '/{slug}/ferien/payment/prepare', name: 'ferien_bezahlung_prepare', methods: ['Get'])]
    public function prepareAction(CheckoutPaymentService $checkoutPaymentService, Request $request, TranslatorInterface $translator, ValidatorInterface $validator, #[MapEntity(mapping: ['slug' => 'slug'])] Stadt $stadt, StamdatenFromCookie $stamdatenFromCookie, CheckoutSepaService $checkoutSepaService)
    {
        if ($stamdatenFromCookie->getStammdatenFromCookie($request, FerienController::BEZEICHNERCOOKIE)) {
            $adresse = $stamdatenFromCookie->getStammdatenFromCookie($request, FerienController::BEZEICHNERCOOKIE);
        }
        $checkoutPaymentService->createPayment($adresse, $request->getClientIp());

        return $this->redirectToRoute('ferien_bezahlung', ['slug' => $stadt->getSlug()]);
    }

    #[Route(path: '/{slug}/ferien/bezahlung', name: 'ferien_bezahlung', methods: ['Get', 'POST'])]
    public function paymentAction(CheckoutPaymentService $checkoutPaymentService, Request $request, TranslatorInterface $translator, ValidatorInterface $validator, #[MapEntity(mapping: ['slug' => 'slug'])] Stadt $stadt, StamdatenFromCookie $stamdatenFromCookie, CheckoutSepaService $checkoutSepaService)
    {
        if ($stamdatenFromCookie->getStammdatenFromCookie($request, FerienController::BEZEICHNERCOOKIE)) {
            $adresse = $stamdatenFromCookie->getStammdatenFromCookie($request, FerienController::BEZEICHNERCOOKIE);
        }
        $payment = $checkoutPaymentService->getNextPayment($adresse);
        if ($payment) {
            $paymentSepa = $checkoutPaymentService->findSepaInEltern($adresse, $payment->getOrganisation());
        } else {
            if ($adresse->getFin() === true) {
                return $this->redirectToRoute('ferien_abschluss', ['slug' => $stadt->getSlug()]);
            }

            return $this->redirectToRoute('ferien_zusammenfassung', ['slug' => $stadt->getSlug()]);
        }

        $form = $this->createForm(PaymentType::class, $paymentSepa);
        $form->handleRequest($request);
        $errors = [];
        if ($form->isSubmitted() && $form->isValid()) {
            $paymentSepa = new PaymentSepa();
            $paymentSepa = $form->getData();
            $errors = $validator->validate($payment);
            if (sizeof($errors) == 0) {
                $checkoutSepaService->generateSepaPayment($adresse, $paymentSepa, $request->getClientIp(), $payment);
                if ($checkoutPaymentService->getNextPayment($adresse) === null) {
                    return $this->redirectToRoute('ferien_zusammenfassung', ['slug' => $stadt->getSlug()]);
                }

                return $this->redirectToRoute('ferien_bezahlung', ['slug' => $stadt->getSlug(), 'snack' => $translator->trans('Fehler. Bitte versuchen Sie es erneut.')]);
            }
        }

        return $this->render('ferien_checkout/bezahlung.html.twig', ['payment' => $payment, 'stadt' => $stadt, 'form' => $form]);
    }
}
