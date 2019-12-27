<?php

namespace App\Repository;

use App\Entity\Ferienblock;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\Validator\Constraints\DateTime;

/**
 * @method Ferienblock|null find($id, $lockMode = null, $lockVersion = null)
 * @method Ferienblock|null findOneBy(array $criteria, array $orderBy = null)
 * @method Ferienblock[]    findAll()
 * @method Ferienblock[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FerienblockRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ferienblock::class);
    }

    // /**
    //  * @return Ferienblock[] Returns an array of Ferienblock objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('f.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Ferienblock
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
    /**
     * @param $price
     * @return Product[]
     */
    public function findFerienblocksFromToday($stadt, \DateTime $start = null, \DateTime $end = null)
    {
        // automatically knows to select Products
        // the "p" is an alias you'll use in the rest of the query
        $today = new \DateTime();
        $qb = $this->createQueryBuilder('f');

        if ($start != null) {
            $qb->andWhere('f.startDate > :start')
                ->setParameter('start', $start);
        }
        if ($end != null) {
            $qb->andWhere('f.endDate < :end')
                ->setParameter('end', $end);
        }
        if ($end === null && $start === null) {
            $qb->
            andWhere('f.endDate >= :today')
            ->setParameter('today', $today);
        }
        $qb->
        andWhere('f.stadt = :stadt')
            ->addOrderBy('f.startDate', 'asc')
            ->setParameter('stadt', $stadt);

        $ferien = $qb->getQuery()->getResult();
        $res = array();
        foreach ($ferien as $data) {
            $res[$data->getStartDate()->format('d.m.Y')][] = $data;
        }
        return $res;
        // to get just one result:
        // $product = ;
    }
}
