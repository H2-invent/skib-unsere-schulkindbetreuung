<?php

namespace App\Repository;

use App\Entity\Anmeldefristen;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Anmeldefristen|null find($id, $lockMode = null, $lockVersion = null)
 * @method Anmeldefristen|null findOneBy(array $criteria, array $orderBy = null)
 * @method Anmeldefristen[]    findAll()
 * @method Anmeldefristen[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AnmeldefristenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Anmeldefristen::class);
    }

    // /**
    //  * @return Anmeldefristen[] Returns an array of Anmeldefristen objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Anmeldefristen
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
