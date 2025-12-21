<?php

namespace App\Repository;

use App\Entity\Active;
use App\Entity\Kind;
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

    public function findBeworbenBlocksBySchule(Schule $schule)
    {
        return $this->createQueryBuilder('z')
            ->innerJoin('z.schule','schule')
            ->innerJoin('z.kinderBeworben','kinder_beworben')
            ->innerJoin('kinder_beworben.eltern','eltern')
            ->andWhere('kinder_beworben.startDate is not NULL')
            ->andWhere('eltern.created_at is not NULL')
            ->andWhere('schule =:schule')
            ->andWhere('z.deleted = :false')
            ->andWhere('SIZE(z.kinderBeworben) > 0')
            ->setParameter('schule', $schule)
            ->setParameter('false', false)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Zeitblock[]
     */
    public function findBeworbenBlocksByKind(Kind $kind): array
    {
        return $this->createQueryBuilder('z')
            ->innerJoin('z.schule', 'schule')
            ->innerJoin('z.kinderBeworben', 'kinder_beworben')
            ->innerJoin('kinder_beworben.eltern', 'eltern')
            ->andWhere('kinder_beworben = :kind')
            ->setParameter('kind', $kind)
            ->andWhere('kinder_beworben.startDate is not NULL')
            ->andWhere('eltern.created_at is not NULL')
            ->andWhere('z.deleted = 0')
            ->getQuery()
            ->getResult()
        ;
    }

     /**
      * @return Zeitblock[] Returns an array of Zeitblock objects
      */
    public function findWartelisteForChild(Kind $kind)
    {
        return $this->createQueryBuilder('z')
            ->innerJoin('z.wartelisteKinder','warteliste_kinder')
            ->andWhere('warteliste_kinder.tracing =:tracing')
            ->setParameter('tracing',$kind->getTracing())
            ->getQuery()
            ->getResult()
        ;
    }

}
