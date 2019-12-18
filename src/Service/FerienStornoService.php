<?php

namespace App\Service;
use App\Entity\KindFerienblock;
use App\Entity\Payment;
use App\Entity\PaymentRefund;
use App\Entity\Stammdaten;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use function Doctrine\ORM\QueryBuilder;


class FerienStornoService
{


    private $em;
    private $translator;
    private $payment;
    private $logger;

    public function __construct(LoggerInterface $logger, EntityManagerInterface $entityManager, TranslatorInterface $translator,CheckoutPaymentService $checkoutPaymentService)
    {
        $this->em = $entityManager;
        $this->translator = $translator;
        $this->payment = $checkoutPaymentService;
        $this->logger = $logger;
    }

    public function toggleBlock(KindFerienblock $kindFerienblock, Stammdaten $stammdaten)
    {
        if ($stammdaten === null) {
            return 0;
        }

        if ($kindFerienblock->getState() == 20) {
            $result['text'] = 'Ferienprogram bereits storniert';
        }
        if ($kindFerienblock->getMarkedAsStorno() === false) {
            $kindFerienblock->setMarkedAsStorno(true);
            $result['cardText'] = $this->translator->trans('Als Storniert vorgemerkt');
            $result['state'] = 20;
        } else {
            $kindFerienblock->setMarkedAsStorno(false);
            $result['cardText'] = $this->translator->trans('Stornieren');
            $result['state'] = 0;
        }

        $result['error'] = 0;
        $this->em->persist($kindFerienblock);
        $this->em->flush();
        return $result;
    }

    public function stornoAbschluss(Stammdaten $stammdaten, $ipAdresse)
    {
        $qb = $this->em->getRepository(KindFerienblock::class)->createQueryBuilder('kinderBlock');
        $qb->innerJoin('kinderBlock.kind', 'kind')
            ->andWhere('kind.eltern = :eltern')
            ->andWhere('kinderBlock.markedAsStorno = true ')
            ->andWhere($qb->expr()->lt('kinderBlock.state', 20))
            ->setParameter('eltern', $stammdaten);
        $query = $qb->getQuery();
        $blocks = $query->getResult();
        $org = array();
        foreach ($blocks as $data) {
            $org[$data->getFerienblock()->getOrganisation()->getId()][]=$data;
            $this->logger->info('storno Stammdaten '.$stammdaten->getId().' : '.$data->getId());
        }
        foreach ($org as $data){
            $organisation = $data[0]->getFerienblock()->getOrganisation();
            $payment = $this->em->getRepository(Payment::class)->findOneBy(array('stammdaten'=>$stammdaten,'organisation'=>$organisation));
            $refund = new PaymentRefund();
            $refund->setSumme(0);
            $refund->setRefundFee($organisation->getStornoGebuehr());
            $refund->setIpAdresse($ipAdresse);
            $refund->setCreatedAt(new \DateTime());
            $refund->setPayment($payment);

            foreach ($data as $block){
                $refund->setSumme($refund->getSumme()+$block->getPreis());
            }

            $this->payment->refund($refund);
            $this->em->persist($refund);
        }

        foreach ($blocks as $data) {
            $data->setState(20);
            $data->setMarkedAsStorno(false);
            $this->em->persist($data);
        }
        $this->em->flush();
    }

}
