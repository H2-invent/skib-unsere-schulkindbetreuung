<?php

namespace App\Repository;

use App\Entity\Stadt;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Stadt|null find($id, $lockMode = null, $lockVersion = null)
 * @method Stadt|null findOneBy(array $criteria, array $orderBy = null)
 * @method Stadt[]    findAll()
 * @method Stadt[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StadtRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Stadt::class);
    }

    // /**
    //  * @return Stadt[] Returns an array of Stadt objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Stadt
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
