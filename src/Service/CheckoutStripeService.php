<?php

namespace App\Service;

use App\Entity\Payment;
use App\Entity\PaymentBraintree;
use App\Entity\PaymentRefund;
use App\Entity\PaymentStripe;
use App\Entity\Stammdaten;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Stripe\Charge;
use Stripe\Stripe;


// <- Add this

class CheckoutStripeService
{


    private $em;
    private $logger;

    public function __construct(LoggerInterface $logger, EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
        $this->logger = $logger;
    }



    public function makeTransaction(PaymentStripe $paymentStripe)
    {
        if ($paymentStripe->getStatus() !== true) {
            $stripe = new Stripe();
            $stripe->setApiKey($paymentStripe->getPayment()->getOrganisation()->getStripeSecret());

            try {
                $charge = new Charge();
                $result = $charge->create(array(
                    'amount' => $paymentStripe->getPayment()->getSumme() * 100,
                    'currency' => 'eur',
                    'description' => $paymentStripe->getPayment()->getOrganisation()->getName(),
                    'source' => $paymentStripe->getChargeId(),
                ));
                if ($result->status == "succeeded") {
                    $paymentStripe->setStatus(true);
                    $paymentStripe->getPayment()->setBezahlt(floatval($result->amount / 100));
                    $paymentStripe->setResult($result->toArray());
                    $paymentStripe->setTransactionId($result->id);
                    $this->em->persist($paymentStripe);
                    $this->em->persist($paymentStripe->getPayment());
                }else{
                    $payment = $paymentStripe->getPayment();
                    $payment->setBraintree(null);
                    $this->em->remove($paymentStripe);
                    $this->em->remove($payment);
                    $paymentStripe = null;
                }
            }catch (\Exception $e){
                $payment = $paymentStripe->getPayment();
                $payment->setBraintree(null);
                $this->em->remove($paymentStripe);
                $this->em->remove($payment);
                $paymentStripe = null;
            }
            $this->em->flush();
        }
        return $paymentStripe;

    }

    public function makeRefund(PaymentRefund $paymentRefund, PaymentBraintree $paymentBraintree)
    {
        //todo refund muss gemacht werden
        $gateway = new Gateway([
            'environment' => $paymentBraintree->getPayment()->getOrganisation()->getBraintreeSandbox()?'sandbox':'production',
            'merchantId' => $paymentBraintree->getPayment()->getOrganisation()->getBraintreeMerchantId(),
            'publicKey' => $paymentBraintree->getPayment()->getOrganisation()->getBraintreePublicKey(),
            'privateKey' => $paymentBraintree->getPayment()->getOrganisation()->getBraintreePrivateKey(),
        ]);

        if (!$paymentRefund->getGezahlt()) {
            $result = $gateway->transaction()->refund(
                $paymentBraintree->getTransactionId(),
                $paymentRefund->getSumme() - $paymentRefund->getRefundFee());
            $paymentRefund->setGezahlt($result->success);
            $this->logger->info('storno Payment: ' . $paymentRefund->getPayment()->getId(), array('RefundId' => $paymentRefund->getId(), 'type' => 'braintree'));
            $this->logger->info('storno Payment: ' . $paymentRefund->getPayment()->getId(), array('IP' => $paymentRefund->getIpAdresse(), 'type' => 'braintree'));
            $this->logger->info('storno Payment: ' . $paymentRefund->getPayment()->getId(), array('SummeGezahlt' => $paymentRefund->getSummeGezahlt(), 'type' => 'braintree'));
            $this->logger->info('storno Payment: ' . $paymentRefund->getPayment()->getId(), array('Summe' => $paymentRefund->getSumme(), 'type' => 'braintree'));
            $this->logger->info('storno Payment: ' . $paymentRefund->getPayment()->getId(), array('RefundFee' => $paymentRefund->getRefundFee(), 'type' => 'braintree'));
            $this->logger->info('storno Payment: ' . $paymentRefund->getPayment()->getId(), array('RefundArt' => $paymentRefund->getTypeAsString(), 'type' => 'braintree'));


            if ($result->success) {
                $paymentRefund->setSummeGezahlt($result->transaction->amount);
                $this->logger->info('storno Payment: ' . $paymentRefund->getPayment()->getId(), array('RefundResult' => $result->success, 'type' => 'braintree'));
                $this->logger->info('storno Payment: ' . $paymentRefund->getPayment()->getId(), array('Transaction Summe' => $result->transaction->amount, 'type' => 'braintree'));
                $this->logger->info('storno Payment: ' . $paymentRefund->getPayment()->getId(), array('Transaction ID' => $result->transaction->id, 'type' => 'braintree'));


            } else {
                $this->logger->error(serialize($result));
                $paymentRefund->setSummeGezahlt(0);
                $paymentRefund->setErrorMessage($result->message);
                $this->logger->info('storno Payment: ' . $paymentRefund->getPayment()->getId(), array('RefundResult' => $result->success, 'type' => 'braintree'));
                 $this->logger->info('storno Payment: ' . $paymentRefund->getPayment()->getId(), array('Transaction ID' => $paymentBraintree->getTransactionId(), 'type' => 'braintree'));
                $this->logger->info('storno Payment: ' . $paymentRefund->getPayment()->getId(), array('Transaction ErrorMessage' => $result->message, 'type' => 'braintree'));

            }
        }
        $this->em->persist($paymentRefund);
        $this->em->flush();
    }

}
