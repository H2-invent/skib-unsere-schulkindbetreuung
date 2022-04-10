<?php

namespace App\Repository;

use App\Entity\Anwesenheit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Anwesenheit|null find($id, $lockMode = null, $lockVersion = null)
 * @method Anwesenheit|null findOneBy(array $criteria, array $orderBy = null)
 * @method Anwesenheit[]    findAll()
 * @method Anwesenheit[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AnwesenheitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Anwesenheit::class);
    }

    // /**
    //  * @return Anwesenheit[] Returns an array of Anwesenheit objects
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
    public function findOneBySomeField($value): ?Anwesenheit
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
