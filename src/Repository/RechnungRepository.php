<?php

namespace App\Repository;

use App\Entity\Rechnung;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Rechnung|null find($id, $lockMode = null, $lockVersion = null)
 * @method Rechnung|null findOneBy(array $criteria, array $orderBy = null)
 * @method Rechnung[]    findAll()
 * @method Rechnung[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RechnungRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Rechnung::class);
    }

    // /**
    //  * @return Rechnung[] Returns an array of Rechnung objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Rechnung
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
