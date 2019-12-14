<?php

namespace App\Repository;

use App\Entity\PaymentRefund;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method PaymentRefund|null find($id, $lockMode = null, $lockVersion = null)
 * @method PaymentRefund|null findOneBy(array $criteria, array $orderBy = null)
 * @method PaymentRefund[]    findAll()
 * @method PaymentRefund[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PaymentRefundRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PaymentRefund::class);
    }

    // /**
    //  * @return PaymentRefund[] Returns an array of PaymentRefund objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?PaymentRefund
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
