<?php

namespace App\Repository;

use App\Entity\Kind;
use App\Entity\Zeitblock;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Kind|null find($id, $lockMode = null, $lockVersion = null)
 * @method Kind|null findOneBy(array $criteria, array $orderBy = null)
 * @method Kind[]    findAll()
 * @method Kind[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class KindRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Kind::class);
    }

    // /**
    //  * @return Kind[] Returns an array of Kind objects
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
    public function findOneBySomeField($value): ?Kind
    {
        return $this->createQueryBuilder('k')
            ->andWhere('k.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
    public function findBeworbenByZeitblock(Zeitblock $zeitblock)
    {
        return $this->createQueryBuilder('k')
            ->innerJoin('k.beworben', 'beworben')
            ->andWhere('beworben = :beworben')
            ->andWhere('k.fin = :true')
            ->setParameter('true', true)
            ->setParameter('beworben', $zeitblock)
            ->getQuery()
            ->getResult();
    }

    public function findActualWorkingCopybyKind(Kind $kind): ?Kind
    {
        return $this->createQueryBuilder('k')
            ->andWhere('k.tracing = :tracingId')
            ->andWhere('k.fin = :false')
            ->andWhere('k.saved = :false')
            ->setParameter('false', false)
            ->setParameter('tracingId', $kind->getTracing())
            ->getQuery()
            ->getOneOrNullResult();
    }
}
