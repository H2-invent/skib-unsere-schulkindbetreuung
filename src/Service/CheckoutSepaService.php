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
    private $translator;
    private $sepaSimpleService;
    private $printRechnungService;
    private $mailerService;
    private $environment;
    private $paymentService;

    public function __construct(CheckoutPaymentService $checkoutPaymentService, Environment $environment, TranslatorInterface $translator, EntityManagerInterface $entityManager, SEPASimpleService $sepaSimpleService, PrintRechnungService $printRechnungService, MailerService $mailerService)
    {
        $this->em = $entityManager;
        $this->translator = $translator;
        $this->sepaSimpleService = $sepaSimpleService;
        $this->printRechnungService = $printRechnungService;
        $this->mailerService = $mailerService;
        $this->environment = $environment;
        $this->paymentService = $checkoutPaymentService;
    }

    public function generateSepaPayment(Stammdaten $stammdaten, PaymentSepa $paymentSepa, $ipAdress): bool
    {
        try {
            foreach ($stammdaten->getPaymentFerien() as $data) {
                $this->em->remove($data);
            }
            $this->em->flush();

            $organisations = $this->paymentService->getOrganisationFromStammdaten($stammdaten);
            foreach ($organisations as $data) {
                $blocks = $this->paymentService->getFerienBlocksKinder($data, $stammdaten);
                $summe = 0.0;
                foreach ($blocks as $data2) {
                    $summe += $data2->getPreis();
                }
                $payment = new Payment();
                $payment->setSumme($summe);
                $payment->setOrganisation($data);
                $payment->setStammdaten($stammdaten);
                $payment->setSepa($paymentSepa);
                $payment->setCreatedAt(new \DateTime());
                $payment->setIpAdresse($ipAdress);
                //todo ist das wirklich bezahlt???
                $payment->setBezahlt($summe);
                $this->em->persist($payment);
            }
            $this->em->flush();

            return true;
        } catch (\Exception $e) {
            return false;
        }


    }


}
