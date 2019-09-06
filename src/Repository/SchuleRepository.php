<?php

namespace App\Repository;

use App\Entity\Schule;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Schule|null find($id, $lockMode = null, $lockVersion = null)
 * @method Schule|null findOneBy(array $criteria, array $orderBy = null)
 * @method Schule[]    findAll()
 * @method Schule[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SchuleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Schule::class);
    }

    // /**
    //  * @return Schule[] Returns an array of Schule objects
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
    public function findOneBySomeField($value): ?Schule
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
