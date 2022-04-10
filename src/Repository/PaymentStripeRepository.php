<?php

namespace App\Repository;

use App\Entity\PaymentStripe;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PaymentStripe|null find($id, $lockMode = null, $lockVersion = null)
 * @method PaymentStripe|null findOneBy(array $criteria, array $orderBy = null)
 * @method PaymentStripe[]    findAll()
 * @method PaymentStripe[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PaymentStripeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PaymentStripe::class);
    }

    // /**
    //  * @return PaymentStripe[] Returns an array of PaymentStripe objects
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
    public function findOneBySomeField($value): ?PaymentStripe
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
