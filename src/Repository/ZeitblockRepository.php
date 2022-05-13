<?php

namespace App\Repository;

use App\Entity\Active;
use App\Entity\Schule;
use App\Entity\Zeitblock;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Zeitblock|null find($id, $lockMode = null, $lockVersion = null)
 * @method Zeitblock|null findOneBy(array $criteria, array $orderBy = null)
 * @method Zeitblock[]    findAll()
 * @method Zeitblock[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ZeitblockRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Zeitblock::class);
    }

       /**
        * @return Zeitblock[] Returns an array of Zeitblock objects
        */

    public function findBeworbenBlocksBySchuleAndSchulfahr(Schule $schule, Active $active)
    {
        return $this->createQueryBuilder('z')
            ->innerJoin('z.schule','schule')
            ->innerJoin('z.active', 'active')
            ->innerJoin('z.kinderBeworben','kinder_beworben')
            ->andWhere('kinder_beworben.fin = :true')
            ->andWhere('active =:schuljahr')
            ->andWhere('schule =:schule')
            ->andWhere('z.deleted = :false')
            ->andWhere('SIZE(z.kinderBeworben) > 0')
            ->setParameter('schule', $schule)
            ->setParameter('schuljahr', $active)
            ->setParameter('false', false)
            ->setParameter('true', true)
            ->getQuery()
            ->getResult()
        ;
    }

    // /**
    //  * @return Zeitblock[] Returns an array of Zeitblock objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('z')
            ->andWhere('z.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('z.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Zeitblock
    {
        return $this->createQueryBuilder('z')
            ->andWhere('z.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

}
