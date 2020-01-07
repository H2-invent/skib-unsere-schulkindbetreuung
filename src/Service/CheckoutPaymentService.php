<?php

namespace App\Service;

use App\Entity\Active;
use App\Entity\Ferienblock;
use App\Entity\Kind;
use App\Entity\KindFerienblock;
use App\Entity\Organisation;
use App\Entity\Payment;
use App\Entity\PaymentRefund;
use App\Entity\PaymentSepa;
use App\Entity\Rechnung;
use App\Entity\Sepa;
use App\Entity\Stadt;

use App\Entity\Stammdaten;

use App\Entity\Zeitblock;
use App\Form\Type\ConfirmType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;

use phpDocumentor\Reflection\Types\Boolean;
use PHPUnit\Util\Json;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use Symfony\Component\Form\Extension\Core\Type\TextType;


use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Security;

use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;
use function Doctrine\ORM\QueryBuilder;


// <- Add this

class CheckoutPaymentService
{


    private $em;
    private $braintree;
    private $logger;
    private $stripe;

    public function __construct(CheckoutStripeService $checkoutStripeService, LoggerInterface $logger, EntityManagerInterface $entityManager, CheckoutBraintreeService $checkoutBraintreeService)
    {
        $this->em = $entityManager;
        $this->braintree = $checkoutBraintreeService;
        $this->logger = $logger;
        $this->stripe = $checkoutStripeService;
    }

    public function getFerienBlocksKinder(Organisation $organisation, Stammdaten $stammdaten): ArrayCollection
    {
        $qb = $this->em->getRepository(KindFerienblock::class)->createQueryBuilder('kFb');
        $qb->innerJoin('kFb.ferienblock', 'ferienBlock')
            ->andWhere('ferienBlock.organisation = :organisation')
            ->innerJoin('kFb.kind', 'kind')
            ->andWhere('kind.eltern = :eltern')
            ->setParameter('eltern', $stammdaten)
            ->setParameter('organisation', $organisation);
        $query = $qb->getQuery();
        return new ArrayCollection($query->getResult());
    }

    public function getOrganisationFromStammdaten(Stammdaten $stammdaten): ArrayCollection
    {
        $qb = $this->em->getRepository(Organisation::class)->createQueryBuilder('org');
        $qb->innerJoin('org.ferienblocks', 'fB')
            ->innerJoin('fB.kindFerienblocks', 'kindFb')
            ->innerJoin('kindFb.kind', 'kind')
            ->andWhere('kind.eltern = :eltern')
            ->setParameter('eltern', $stammdaten);
        $query = $qb->getQuery();
        return new ArrayCollection($query->getResult());
    }

    public function getPaymentWithEmptyNonce(Stammdaten $stammdaten): ArrayCollection
    {
        $qb = $this->em->getRepository(Payment::class)->createQueryBuilder('pay');
        $qb->innerJoin('pay.braintree', 'bT')
            ->andWhere($qb->expr()->isNull('bT.nonce'))
            ->andWhere('pay.stammdaten = :eltern')
            ->setParameter('eltern', $stammdaten);
        $query = $qb->getQuery();
        return new ArrayCollection($query->getResult());
    }

    public function createPayment(Stammdaten $stammdaten, $ipAdresse)
    {
        $res = false;
        try {
            $organisations = $this->getOrganisationFromStammdaten($stammdaten);
            foreach ($organisations as $data) {
                if (!$this->em->getRepository(Payment::class)->findOneBy(array('organisation' => $data, 'stammdaten' => $stammdaten))) {
                    $res = true;
                    $blocks = $this->getFerienBlocksKinder($data, $stammdaten);
                    $summe = 0.0;
                    foreach ($blocks as $data2) {
                        $summe += $data2->getPreis();
                    }
                    $payment = new Payment();
                    $payment->setSumme($summe);
                    $payment->setOrganisation($data);
                    $payment->setStammdaten($stammdaten);
                    $payment->setCreatedAt(new \DateTime());
                    $payment->setIpAdresse($ipAdresse);
                    $payment->setBezahlt(0);
                    $payment->setUid(md5(uniqid()));
                    $payment->setFinished(false);
                   $this->em->persist($payment);
                }
            }
            $this->em->flush();
            return $res;
        } catch (\Exception $e) {
            return $res;
        }
    }

    public function findSepaInEltern(Stammdaten $stammdaten, Organisation $organisation)
    {
        $first = $this->em->getRepository(PaymentSepa::class)->createQueryBuilder('sepa');
        $first->innerJoin('sepa.payments', 'payments')
            ->andWhere('payments.organisation = :org')
            ->andWhere('payments.stammdaten = :stammdaten')
            ->setParameter('org', $organisation)
            ->setParameter('stammdaten', $stammdaten);
        $query1 = $first->getQuery();
        $firstRes = $query1->getFirstResult();
        if ($firstRes) {
            return $firstRes;
        }

        $qb = $this->em->getRepository(PaymentSepa::class)->createQueryBuilder('sepa');
        $qb->innerJoin('sepa.payments', 'payments')
            ->andWhere('payments.stammdaten = :stammdate')
            ->setParameter('stammdate', $stammdaten);
        $query = $qb->getQuery();
        $secRes = $query->getFirstResult();
        if ($secRes) {
            return $secRes;
        }
        return new PaymentSepa();

    }

    public function cleanPayment(Payment $payment): bool
    {
        try {
            if ($payment->getBraintree()) {
                $bT = $payment->getBraintree();
                $payment->setBraintree(null);
                $this->em->remove($bT);
            }
            if ($payment->getSepa()) {
                $sepa = $payment->getSepa();
                $payment->setSepa(null);
                $this->em->remove($sepa);
            }
            $this->em->flush();

            return true;
        } catch (\Exception $e) {
            return false;
        }

    }

    public function getNextPayment(Stammdaten $stammdaten)
    {
        $payment = $this->em->getRepository(Payment::class)->findBy(array('stammdaten' => $stammdaten, 'finished' => false), array('id' => 'asc'));

        if (sizeof($payment) > 0) {
            return $payment[0];
        }
        return null;

    }

    public function makePayment(Stammdaten $stammdaten): float
    {
        $payments = $this->em->getRepository(Payment::class)->findBy(array('stammdaten' => $stammdaten));

        $summe = 0;
        foreach ($payments as $data) {
            if ($data->getSepa()) {
                $data->setBezahlt($data->getSumme());
                $this->setKindFerienBlockAsPAyed($data->getOrganisation(),$data->getStammdaten());
            }

            if ($data->getBraintree()) {
                $res = $this->braintree->makeTransaction($data->getBraintree());
                if ($res === null) {
                    $summe++;
                }
                if($data->getBraintree()->getSuccess() === true){
                    $this->setKindFerienBlockAsPAyed($data->getOrganisation(),$data->getStammdaten());
                }
            }
            if ($data->getPaymentStripe()) {
                $res = $this->stripe->makeTransaction($data->getPaymentStripe());
                if ($res === null) {
                    $summe++;
                }
                if($data->getPaymentStripe()->getStatus() === true){
                    $this->setKindFerienBlockAsPAyed($data->getOrganisation(),$data->getStammdaten());
                }
            }
            $summe += $data->getSumme() - $data->getBezahlt();
        }
        return $summe;
    }

    public function refund(PaymentRefund $paymentRefund)
    {
        $payment = $paymentRefund->getPayment();

        if ($payment->getSepa()) {
            $paymentRefund->setRefundType(0);
            $paymentRefund->setGezahlt(false);
            $paymentRefund->setSummeGezahlt($paymentRefund->getSumme() - $paymentRefund->getRefundFee());
            $this->logger->info('storno Payment: ' . $paymentRefund->getPayment()->getId(), array('RefundId' => $paymentRefund->getId(), 'type' => 'sepa'));
            $this->logger->info('storno Payment: ' . $paymentRefund->getPayment()->getId(), array('IP' => $paymentRefund->getIpAdresse(), 'type' => 'sepa'));
            $this->logger->info('storno Payment: ' . $paymentRefund->getPayment()->getId(), array('SummeGezahlt' => $paymentRefund->getSummeGezahlt(), 'type' => 'sepa'));
            $this->logger->info('storno Payment: ' . $paymentRefund->getPayment()->getId(), array('Summe' => $paymentRefund->getSumme(), 'type' => 'sepa'));
            $this->logger->info('storno Payment: ' . $paymentRefund->getPayment()->getId(), array('RefundFee' => $paymentRefund->getRefundFee(), 'type' => 'sepa'));
            $this->logger->info('storno Payment: ' . $paymentRefund->getPayment()->getId(), array('RefundArt' => $paymentRefund->getTypeAsString(), 'type' => 'sepa'));

        }
        if ($payment->getBraintree()) {
            $paymentRefund->setRefundType(1);
            $paymentRefund->setGezahlt(false);
            $this->braintree->makeRefund($paymentRefund, $payment->getBraintree());
        }
        if ($payment->getPaymentStripe()) {
            $paymentRefund->setRefundType(1);
            $paymentRefund->setGezahlt(false);
            $this->stripe->makeRefund($paymentRefund, $payment->getPaymentStripe());
        }
        return $paymentRefund->getGezahlt();

    }

    public function makeRefundPAyment(PaymentRefund $paymentRefund)
    {
        $payment = $paymentRefund->getPayment();

        if ($payment->getSepa()) {
            $paymentRefund->setGezahlt(true);
            $this->em->persist($paymentRefund);
            $this->em->flush();
            $this->logger->info('storno Payment: ' . $paymentRefund->getPayment()->getId(), array('RefundId' => $paymentRefund->getId(), 'type' => 'sepa'));
            $this->logger->info('storno Payment: ' . $paymentRefund->getPayment()->getId(), array('Set Getzahlt' => $paymentRefund->getGezahlt(), 'type' => 'sepa'));

        }
        if ($payment->getBraintree()) {
            $this->braintree->makeRefund($paymentRefund, $payment->getBraintree());
        }
    return $paymentRefund->getGezahlt();
    }
    public function setKindFerienBlockAsPAyed(Organisation $organisation, Stammdaten $stammdaten){
        $qb = $this->em->getRepository(KindFerienblock::class)->createQueryBuilder('kind_ferienblock')
            ->innerJoin('kind_ferienblock.ferienblock','ferienblock')
            ->innerJoin('kind_ferienblock.kind','kind')
            ->andWhere('kind.eltern = :stammdaten')
            ->andWhere('ferienblock.organisation = :org')
            ->setParameter('stammdaten',$stammdaten)
            ->setParameter('org',$organisation);
        $query = $qb->getQuery();
        $kindFerienblock = $query->getResult();
        foreach ($kindFerienblock as $data){
            $data->setBezahlt(true);
            $this->em->persist($data);
        }
        $this->em->flush();
    }
}
