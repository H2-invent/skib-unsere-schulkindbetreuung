<?php

namespace App\Repository;

use App\Entity\PaymentSepa;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PaymentSepa|null find($id, $lockMode = null, $lockVersion = null)
 * @method PaymentSepa|null findOneBy(array $criteria, array $orderBy = null)
 * @method PaymentSepa[]    findAll()
 * @method PaymentSepa[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PaymentSepaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PaymentSepa::class);
    }

    // /**
    //  * @return PaymentSepa[] Returns an array of PaymentSepa objects
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
    public function findOneBySomeField($value): ?PaymentSepa
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
