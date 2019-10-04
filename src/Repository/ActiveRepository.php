<?php

namespace App\Repository;

use App\Entity\Active;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Active|null find($id, $lockMode = null, $lockVersion = null)
 * @method Active|null findOneBy(array $criteria, array $orderBy = null)
 * @method Active[]    findAll()
 * @method Active[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ActiveRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Active::class);
    }

    // /**
    //  * @return Active[] Returns an array of Active objects
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
    public function findOneBySomeField($value): ?Active
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
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
    public function findActiveSchuljahrFromCity($stadt)
    {
        // automatically knows to select Products
        // the "p" is an alias you'll use in the rest of the query
        $today = new \DateTime();
        $qb = $this->createQueryBuilder('a')
            ->andWhere('a.stadt = :stadt')
            ->andWhere('a.anmeldeStart <= :today')
            ->andWhere('a.anmeldeEnde >= :today')
            ->setParameter('today', $today)
            ->setParameter('stadt', $stadt)
            ->getQuery()
            ->setMaxResults(1);

        return $qb->getOneOrNullResult();

        // to get just one result:
        // $product = ;
    }

    /**
     * @param $price
     * @return Product[]
     */
    public function findSchuljahrFromCity($stadt)
    {
        // automatically knows to select Products
        // the "p" is an alias you'll use in the rest of the query
        $today = new \DateTime();
        $qb = $this->createQueryBuilder('a')
            ->andWhere('a.stadt = :stadt')
            ->andWhere('a.von <= :today')
            ->andWhere('a.bis >= :today')
            ->setParameter('today', $today)
            ->setParameter('stadt', $stadt)
            ->getQuery()
            ->setMaxResults(1);

        return $qb->getOneOrNullResult();

        // to get just one result:
        // $product = ;
    }
}
