<?php

namespace App\Repository;

use App\Entity\PaymentBraintree;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PaymentBraintree|null find($id, $lockMode = null, $lockVersion = null)
 * @method PaymentBraintree|null findOneBy(array $criteria, array $orderBy = null)
 * @method PaymentBraintree[]    findAll()
 * @method PaymentBraintree[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PaymentBraintreeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PaymentBraintree::class);
    }

    // /**
    //  * @return PaymentBraintree[] Returns an array of PaymentBraintree objects
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
    public function findOneBySomeField($value): ?PaymentBraintree
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
