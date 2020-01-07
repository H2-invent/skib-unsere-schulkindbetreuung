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
use Stripe\Refund;
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

    public function makeRefund(PaymentRefund $paymentRefund, PaymentStripe $paymentStripe)
    {
        $stripe = new Stripe();
        $stripe->setApiKey($paymentStripe->getPayment()->getOrganisation()->getStripeSecret());

        //todo refund muss gemacht werden
        $reType = 'stripe';
        if (!$paymentRefund->getGezahlt()) {
            $re = (new Refund())->create(array(
                'amount'=>($paymentRefund->getSumme()-$paymentRefund->getRefundFee())*100,
                'charge'=>$paymentStripe->getTransactionId(),

            ));
            dump($re);
            $paymentRefund->setGezahlt($re->status=="succeeded"?true:false);
            $this->logger->info('storno Payment: ' . $paymentRefund->getPayment()->getId(), array('RefundId' => $paymentRefund->getId(), 'type' => $reType));
            $this->logger->info('storno Payment: ' . $paymentRefund->getPayment()->getId(), array('IP' => $paymentRefund->getIpAdresse(), 'type' => $reType));
            $this->logger->info('storno Payment: ' . $paymentRefund->getPayment()->getId(), array('SummeGezahlt' => $paymentRefund->getSummeGezahlt(), 'type' => $reType));
            $this->logger->info('storno Payment: ' . $paymentRefund->getPayment()->getId(), array('Summe' => $paymentRefund->getSumme(), 'type' => $reType));
            $this->logger->info('storno Payment: ' . $paymentRefund->getPayment()->getId(), array('RefundFee' => $paymentRefund->getRefundFee(), 'type' => $reType));
            $this->logger->info('storno Payment: ' . $paymentRefund->getPayment()->getId(), array('RefundArt' => $paymentRefund->getTypeAsString(), 'type' => $reType));


            if ($paymentRefund->getGezahlt()) {
                $paymentRefund->setSummeGezahlt($re->amount/100);
                $this->logger->info('storno Payment: ' . $paymentRefund->getPayment()->getId(), array('RefundResult' => $re->status, 'type' => $reType));
                $this->logger->info('storno Payment: ' . $paymentRefund->getPayment()->getId(), array('Transaction Summe' => $re->amount, 'type' => $reType));
                $this->logger->info('storno Payment: ' . $paymentRefund->getPayment()->getId(), array('Transaction ID' => $re->id, 'type' => $reType));


            } else {
                $this->logger->error(serialize($re->toArray()));
                $paymentRefund->setSummeGezahlt(0);
                $paymentRefund->setErrorMessage($re->failure_reason);
                $this->logger->info('storno Payment: ' . $paymentRefund->getPayment()->getId(), array('RefundResult' => $re->status, 'type' => $reType));
                 $this->logger->info('storno Payment: ' . $paymentRefund->getPayment()->getId(), array('Transaction ID' => $paymentStripe->getTransactionId(), 'type' => $reType));
                $this->logger->info('storno Payment: ' . $paymentRefund->getPayment()->getId(), array('Transaction ErrorMessage' => $re->failure_reason, 'type' => $reType));

            }
        }
        $this->em->persist($paymentRefund);
        $this->em->flush();
    }

}
