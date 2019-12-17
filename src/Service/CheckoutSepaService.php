<?php

namespace App\Service;

use App\Entity\Active;
use App\Entity\Ferienblock;
use App\Entity\Kind;
use App\Entity\KindFerienblock;
use App\Entity\Organisation;
use App\Entity\Payment;
use App\Entity\PaymentSepa;
use App\Entity\Rechnung;
use App\Entity\Sepa;
use App\Entity\Stadt;

use App\Entity\Stammdaten;

use App\Entity\Zeitblock;
use App\Form\Type\ConfirmType;
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

class CheckoutSepaService
{


    private $em;

    private $paymentService;

    public function __construct(CheckoutPaymentService $checkoutPaymentService, EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
        $this->paymentService = $checkoutPaymentService;
    }

    public function generateSepaPayment(Stammdaten $stammdaten, PaymentSepa $paymentSepa, $ipAdress, Payment $payment): bool
    {
       try {
       // remove all paymants from the payment Type (sepa ans Braintree)
        $this->paymentService->cleanPayment($payment);
        $payment->setSepa($paymentSepa);
        $payment->setBezahlt($payment->getSumme());
        $payment->setFinished(true);
        $this->em->persist($payment);
        $this->em->flush();
        return true;
         } catch (\Exception $e) {
             return false;
        }


    }


}
