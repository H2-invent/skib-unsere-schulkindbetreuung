<?php

namespace App\Service;

use App\Entity\Payment;
use App\Entity\PaymentBraintree;
use App\Entity\PaymentRefund;
use App\Entity\Stammdaten;
use Braintree\Gateway;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;


// <- Add this

class CheckoutBraintreeService
{


    private $em;
    private $logger;

    public function __construct(LoggerInterface $logger, EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
        $this->logger = $logger;
    }

    public function prepareBraintree(Stammdaten $stammdaten, $ipAdresse, Payment $payment): bool
    {
        try {
            if ($payment->getBraintree()) {
                $this->em->remove($payment->getBraintree());
                $this->em->flush();
            }
            $braintree = new PaymentBraintree();
            $gateway = new Gateway([
                'environment' => $payment->getOrganisation()->getBraintreeSandbox()?'sandbox':'production',
                'merchantId' => $payment->getOrganisation()->getBraintreeMerchantId(),
                'publicKey' => $payment->getOrganisation()->getBraintreePublicKey(),
                'privateKey' => $payment->getOrganisation()->getBraintreePrivateKey(),
            ]);
            $clientToken = $gateway->clientToken()->generate();
            $braintree->setIpAdresse($ipAdresse);
            $braintree->setCreatedAt(new \DateTime());
            $braintree->setPayment($payment);
            $braintree->setToken($clientToken);
            $this->em->persist($braintree);
            $this->em->flush();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function makeTransaction(PaymentBraintree $paymentBraintree)
    {
        if ($paymentBraintree->getSuccess() != true) {
            $gateway = new Gateway([
                'environment' => $paymentBraintree->getPayment()->getOrganisation()->getBraintreeSandbox()?'sandbox':'production',
                'merchantId' => $paymentBraintree->getPayment()->getOrganisation()->getBraintreeMerchantId(),
                'publicKey' => $paymentBraintree->getPayment()->getOrganisation()->getBraintreePublicKey(),
                'privateKey' => $paymentBraintree->getPayment()->getOrganisation()->getBraintreePrivateKey(),
            ]);

            $result = $gateway->transaction()->sale([
                'amount' => $paymentBraintree->getPayment()->getSumme(),
                'paymentMethodNonce' => $paymentBraintree->getNonce(),
                'options' => [
                    'submitForSettlement' => True
                ]
            ]);
            $paymentBraintree->setSuccess($result->success);

            if ($result->success) {
                $paymentBraintree->getPayment()->setBezahlt(floatval($result->transaction->amount));
                $paymentBraintree->setTransactionId($result->transaction->id);
                $this->em->persist($paymentBraintree);
                $this->em->persist($paymentBraintree->getPayment());
            } else {
                $payment = $paymentBraintree->getPayment();
                $payment->setBraintree(null);
                $this->em->remove($paymentBraintree);
                $this->em->remove($payment);
                $paymentBraintree = null;
            }
            $this->em->flush();
        }
        return $paymentBraintree;

    }

    public function makeRefund(PaymentRefund $paymentRefund, PaymentBraintree $paymentBraintree)
    {
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
