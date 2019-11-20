<?php

namespace App\Repository;

use App\Entity\Ferienblock;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Ferienblock|null find($id, $lockMode = null, $lockVersion = null)
 * @method Ferienblock|null findOneBy(array $criteria, array $orderBy = null)
 * @method Ferienblock[]    findAll()
 * @method Ferienblock[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FerienblockRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ferienblock::class);
    }

    // /**
    //  * @return Ferienblock[] Returns an array of Ferienblock objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('f.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Ferienblock
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
