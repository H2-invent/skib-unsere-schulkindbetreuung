<?php

namespace App\Service;

use App\Entity\Payment;
use App\Entity\PaymentSepa;
use App\Entity\Stammdaten;
use Doctrine\ORM\EntityManagerInterface;

// <- Add this

class CheckoutSepaService
{
    public function __construct(
        private CheckoutPaymentService $paymentService,
        private EntityManagerInterface $em,
    ) {
    }

    public function generateSepaPayment(Stammdaten $stammdaten, PaymentSepa $paymentSepa, $ipAdress, Payment $payment): bool
    {
        try {
            // remove all paymants from the payment Type (sepa ans Braintree)
            $this->paymentService->cleanPayment($payment);
            $payment->setSepa($paymentSepa);
            $payment->setBezahlt($payment->getSumme());
            $payment->setFinished(true);
            $payment->setArtString('SEPA');
            $this->em->persist($payment);
            $this->em->flush();

            return true;
        } catch (\Exception) {
            return false;
        }
    }
}
