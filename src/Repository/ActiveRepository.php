<?php

namespace App\Repository;

use App\Entity\Active;
use App\Entity\Stadt;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use function Doctrine\ORM\QueryBuilder;

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

    public function findSchuleBetweentwoDates(\DateTime $von, \DateTime $bis, Stadt $stadt)
    {
        // automatically knows to select Products
        // the "p" is an alias you'll use in the rest of the query

        $qb = $this->createQueryBuilder('a')
            ->andWhere('a.stadt = :stadt')
            ->andWhere('a.von <= :von')
            ->andWhere('a.bis >= :bis')
            ->setParameter('von', $von)
            ->setParameter('bis', $bis)
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
    public function findSchuljahrFromCity(Stadt $stadt)
    {
        // automatically knows to select Products
        // the "p" is an alias you'll use in the rest of the query
        $today = new \DateTime();
        $qb = $this->createQueryBuilder('a');
        $qb
            ->andWhere('a.stadt = :stadt');
            $qb->expr()->orX()->add(
                $qb->expr()->andX(
                    $qb->expr()->lte('a.von', $today),
                    $qb->expr()->gte('a.bis', $today)
                )

            )
            ->add(
                $qb->expr()->andX(
                    $qb->expr()->lte('a.anmeldeStart', $today),
                    $qb->expr()->gte('a.anmeldeEnde', $today)
                )
            );

            $qb
            ->setParameter('stadt', $stadt)
            ->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();

        // to get just one result:
        // $product = ;
    }
}
