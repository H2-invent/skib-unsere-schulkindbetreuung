<?php

namespace App\Repository;

use App\Entity\Abwesend;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Abwesend|null find($id, $lockMode = null, $lockVersion = null)
 * @method Abwesend|null findOneBy(array $criteria, array $orderBy = null)
 * @method Abwesend[]    findAll()
 * @method Abwesend[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AbwesendRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Abwesend::class);
    }

    // /**
    //  * @return Abwesend[] Returns an array of Abwesend objects
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
    public function findOneBySomeField($value): ?Abwesend
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
