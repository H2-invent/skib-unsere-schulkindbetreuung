<?php

namespace App\Repository;

use App\Entity\Kundennummern;
use App\Entity\Organisation;
use App\Entity\Stammdaten;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Kundennummern|null find($id, $lockMode = null, $lockVersion = null)
 * @method Kundennummern|null findOneBy(array $criteria, array $orderBy = null)
 * @method Kundennummern[]    findAll()
 * @method Kundennummern[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class KundennummernRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Kundennummern::class);
    }

    // /**
    //  * @return Kundennummern[] Returns an array of Kundennummern objects
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
    public function findOneBySomeField($value): ?Kundennummern
    {
        return $this->createQueryBuilder('k')
            ->andWhere('k.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

     /**
      * @return Kundennummern[] Returns an array of Kundennummern objects
      */
    public function findAllKundennummernByStammdatenAndOrganisation(Stammdaten $stammdaten, Organisation $organisation)
    {
        return $this->createQueryBuilder('k')
            ->innerJoin('k.stammdaten','stammdaten')
            ->andWhere('stammdaten.tracing = :tracing')->setParameter('tracing',$stammdaten->getTracing())
            ->andWhere('k.organisation =:organisation')->setParameter('organisation',$organisation)
            ->getQuery()
            ->getResult()
        ;
    }

}
