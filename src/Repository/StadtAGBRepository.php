<?php

namespace App\Repository;

use App\Entity\StadtAGB;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method StadtAGB|null find($id, $lockMode = null, $lockVersion = null)
 * @method StadtAGB|null findOneBy(array $criteria, array $orderBy = null)
 * @method StadtAGB[]    findAll()
 * @method StadtAGB[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StadtAGBRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StadtAGB::class);
    }

    // /**
    //  * @return StadtAGB[] Returns an array of StadtAGB objects
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
    public function findOneBySomeField($value): ?StadtAGB
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
