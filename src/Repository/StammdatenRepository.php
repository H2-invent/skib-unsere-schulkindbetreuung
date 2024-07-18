<?php

namespace App\Repository;

use App\Entity\Kind;
use App\Entity\Stammdaten;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Stammdaten|null find($id, $lockMode = null, $lockVersion = null)
 * @method Stammdaten|null findOneBy(array $criteria, array $orderBy = null)
 * @method Stammdaten[]    findAll()
 * @method Stammdaten[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StammdatenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Stammdaten::class);
    }

    // /**
    //  * @return Stammdaten[] Returns an array of Stammdaten objects
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
    public function findOneBySomeField($value): ?Stammdaten
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function findActualStammdatenByUid($uid, $kindID= null): ?Stammdaten
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.uid = :uid')
            ->setParameter('uid', $uid)
            ->andWhere('s.created_at IS NULL')
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findWorkingCopyStammdatenByStammdaten(Stammdaten $stammdaten): ?Stammdaten
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.tracing = :tracing')
            ->setParameter('tracing', $stammdaten->getTracing())
            ->andWhere('s.created_at IS NULL')
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findLatestStammdatenByStartDate(Stammdaten $stammdaten): ?Stammdaten
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.tracing = :tracing')
            ->setParameter('tracing', $stammdaten->getTracing())
            ->andWhere('s.created_at IS NOT NULL')
            ->orderBy('s.startDate', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findlatestStammdatenfromKind(Kind $kind): ?Stammdaten
    {

        $tracing = $kind->getEltern()->getTracing();

        return $this->createQueryBuilder('s')
            ->andWhere('s.tracing = :tracing')->setParameter('tracing', $tracing)
            ->andWhere('s.created_at IS NOT NULL')
            ->orderBy('s.created_at', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findStammdatenfromKindforSpecificDate(Kind $kind, \DateTime $dateTime, $demo = false): ?Stammdaten
    {

        $tracing = $kind->getEltern()->getTracing();

        $qb = $this->createQueryBuilder('s')
            ->andWhere('s.tracing = :tracing')->setParameter('tracing', $tracing);
        if (!$demo) {
            $qb->andWhere('s.created_at IS NOT NULL');
        }

        return $qb->andWhere('s.startDate <= :datetime')->setParameter('datetime', $dateTime)
            ->andWhere('s.startDate IS NOT NULL')
            ->orderBy('s.startDate', 'DESC')
            ->addOrderBy('s.created_at','DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

    }


    public function findlatestStammdatenfromStammdaten(Stammdaten $stammdaten): ?Stammdaten
    {

        $tracing = $stammdaten->getTracing();

        return $this->createQueryBuilder('s')
            ->andWhere('s.tracing = :tracing')->setParameter('tracing', $tracing)
            ->andWhere('s.created_at IS NOT NULL')
            ->andWhere('s.startDate IS NOT NULL')
            ->orderBy('s.startDate', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findStammdatenFromStammdatenByDate(Stammdaten $stammdaten, \DateTime $dateTime = null): ?Stammdaten
    {

        if (!$dateTime) {
            $dateTime = new \DateTime();
        }
        $tracing = $stammdaten->getTracing();

        return $this->createQueryBuilder('s')
            ->andWhere('s.tracing = :tracing')->setParameter('tracing', $tracing)
            ->andWhere('s.created_at IS NOT NULL')
            ->andWhere('s.startDate IS NOT NULL')
            ->andWhere('s.startDate <= :datetime')->setParameter('datetime', $dateTime)
            ->orderBy('s.startDate', 'DESC')
            ->orderBy('s.created_at', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return Stammdaten[] Returns an array of Stammdaten objects
     */
    public function findHistoryStammdaten(Stammdaten $stammdaten)
    {

        $tracing = $stammdaten->getTracing();

        return $this->createQueryBuilder('s')
            ->andWhere('s.tracing = :tracing')->setParameter('tracing', $tracing)
            ->andWhere('s.created_at IS NOT NULL')
            ->orderBy('s.created_at', 'ASC')
            ->getQuery()
            ->getResult();
    }

}
