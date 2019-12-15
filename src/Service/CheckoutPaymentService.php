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
use Doctrine\Common\Collections\ArrayCollection;
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
use function Doctrine\ORM\QueryBuilder;


// <- Add this

class CheckoutPaymentService
{


    private $em;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;

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
        try {
            foreach ($stammdaten->getPaymentFerien() as $data) {
                $this->em->remove($data);
            }
            $this->em->flush();

            $organisations = $this->getOrganisationFromStammdaten($stammdaten);
            foreach ($organisations as $data) {
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
            $this->em->flush();

            return true;
        } catch (\Exception $e) {
            return false;
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
        $payment = $this->em->getRepository(Payment::class)->findBy(array('stammdaten' => $stammdaten,'finished'=>false), array('id' => 'asc'));

        if (sizeof($payment) > 0) {
            return $payment[0];
        }
        return null;

    }
}
