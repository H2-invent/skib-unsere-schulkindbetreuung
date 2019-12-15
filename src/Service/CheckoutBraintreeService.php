<?php

namespace App\Service;

use App\Entity\Active;
use App\Entity\Ferienblock;
use App\Entity\Kind;
use App\Entity\KindFerienblock;
use App\Entity\Organisation;
use App\Entity\Payment;
use App\Entity\PaymentBraintree;
use App\Entity\PaymentSepa;
use App\Entity\Rechnung;
use App\Entity\Sepa;
use App\Entity\Stadt;

use App\Entity\Stammdaten;

use App\Entity\Zeitblock;
use App\Form\Type\ConfirmType;
use Braintree\Gateway;
use Doctrine\ORM\EntityManagerInterface;

use phpDocumentor\Reflection\Types\Boolean;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use Symfony\Component\Form\Extension\Core\Type\TextType;


use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Security;

use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;


// <- Add this

class CheckoutBraintreeService
{


    private $em;


    private $paymentService;

    public function __construct(CheckoutPaymentService $checkoutPaymentService, EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
        $this->paymentService = $checkoutPaymentService;
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
            'environment' => 'sandbox',
            //todo hier kommt dann der KEy der Org hin
            'merchantId' => '65xmpcc6hh6khg5d',
            'publicKey' => 'wzkfsj9n2kbyytfp',
            'privateKey' => 'a153a39aaef70466e97773a120b95f91',
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

}
