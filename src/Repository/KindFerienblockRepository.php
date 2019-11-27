<?php

namespace App\Repository;

use App\Entity\KindFerienblock;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method KindFerienblock|null find($id, $lockMode = null, $lockVersion = null)
 * @method KindFerienblock|null findOneBy(array $criteria, array $orderBy = null)
 * @method KindFerienblock[]    findAll()
 * @method KindFerienblock[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class KindFerienblockRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, KindFerienblock::class);
    }

    // /**
    //  * @return KindFerienblock[] Returns an array of KindFerienblock objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('k')
            ->andWhere('k.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('k.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?KindFerienblock
    {
        return $this->createQueryBuilder('k')
            ->andWhere('k.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
