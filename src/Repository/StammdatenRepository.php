<?php

namespace App\Repository;

use App\Entity\Stammdaten;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Stammdaten|null find($id, $lockMode = null, $lockVersion = null)
 * @method Stammdaten|null findOneBy(array $criteria, array $orderBy = null)
 * @method Stammdaten[]    findAll()
 * @method Stammdaten[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StammdatenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Stammdaten::class);
    }

    // /**
    //  * @return Stammdaten[] Returns an array of Stammdaten objects
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
    public function findOneBySomeField($value): ?Stammdaten
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
