<?php

namespace App\Repository;

use App\Entity\Ferienblock;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

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
    public function findFerienblocksFromToday($stadt)
    {
        // automatically knows to select Products
        // the "p" is an alias you'll use in the rest of the query
        $today = new \DateTime();
        $qb = $this->createQueryBuilder('f')
            ->andWhere('f.startDate > :today')
            ->andWhere('f.stadt <= :stadt')
            ->addOrderBy('f.startDate','asc')
            ->setParameter('today', $today)
            ->setParameter('stadt', $stadt)
            ->getQuery();
            //->setMaxResults(1);

        $ferien= $qb->getResult();
        $res = array();
        foreach ($ferien as $data){
            $res[$data->getStartDate()->format('d.m.Y')][] = $data;
        }
        return $res;
        // to get just one result:
        // $product = ;
    }
}
