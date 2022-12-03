<?php

namespace App\Repository;

use App\Entity\Kind;
use App\Entity\Stammdaten;
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
            ->innerJoin('k.eltern', 'eltern')
            ->andWhere('beworben = :beworben')
            ->andWhere('k.startDate is not NULL')
            ->andWhere('eltern.created_at is not NULL')
            ->setParameter('beworben', $zeitblock)
            ->getQuery()
            ->getResult();
    }

    public function findActualWorkingCopybyKind(Kind $kind): ?Kind
    {
        return $this->createQueryBuilder('k')
            ->innerJoin('k.eltern', 'eltern')
            ->andWhere('eltern.created_at is NULL')
            ->andWhere('k.tracing = :tracingId')
            ->setParameter('tracingId', $kind->getTracing())
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return Kind[] Returns an array of Kind objects
     */

    public function findHistoryOfThisChild(Kind $kind)
    {
        return $this->createQueryBuilder('k')
            ->innerJoin('k.eltern', 'eltern')
            ->andWhere('k.tracing = :tracing')
            ->andWhere('k.startDate is not NULL')
            ->andWhere('eltern.created_at is not null')
            ->setParameter('tracing', $kind->getTracing())
            ->addOrderBy('k.startDate', 'ASC')
            ->addOrderBy('eltern.created_at', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Kind[] Returns an array of Kind objects
     */
    public function findAllChildrenWithHistoryFromParent(Stammdaten $stammdaten)
    {
        return $this->createQueryBuilder('k')
            ->andWhere('k.startDate is not NULL ')
            ->innerJoin('k.eltern', 'eltern')
            ->andWhere('eltern.created_at IS NOT NULL')
            ->andWhere('eltern.tracing =:tracing')->setParameter('tracing', $stammdaten->getTracing())
            ->orderBy('k.startDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findLatestKindForDate(Kind $kind, \DateTime $dateTime): ?Kind
    {
        $kinder = $this->createQueryBuilder('k')
            ->andWhere('k.tracing = :tracing')->setParameter('tracing', $kind->getTracing())
            ->innerJoin('k.eltern', 'eltern')
            ->andWhere('eltern.created_at IS NOT NULL')
            ->andWhere('k.startDate <= :now')->setParameter('now', $dateTime)
            ->andWhere('k.startDate is NOT NULL')
            ->orderBy('k.startDate', 'ASC')
            ->getQuery()
            ->getResult();
        $kind = null;
        foreach ($kinder as $data) {
            if (!$kind) {
                $kind = $data;
            }
            if ($kind->getStartDate() < $data->getStartDate()) {
                $kind = $data;
            } elseif ($kind->getStartDate() == $data->getStartDate()) {
                if ($kind->getEltern()->getCreatedAt() < $data->getEltern()->getCreatedAt()) {
                    $kind = $data;
                }
            }
        }
        return $kind;
    }

}
