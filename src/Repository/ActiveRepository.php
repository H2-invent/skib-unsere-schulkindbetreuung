<?php

namespace App\Repository;

use App\Entity\Active;
use App\Entity\Stadt;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
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
     * @return Active
     */
    public function findActiveSchuljahrFromCity(Stadt $stadt):?Active
    {
        $today = new \DateTime();
        $qb = $this->createQueryBuilder('a')
            ->andWhere('a.stadt = :stadt')
            ->andWhere('a.anmeldeStart <= :today')
            ->andWhere('a.bis >= :today')
            ->setParameter('today', $today)
            ->setParameter('stadt', $stadt)
            ->orderBy('a.von', 'ASC')
            ->getQuery()
            ->setMaxResults(1);

        return $qb->getOneOrNullResult();

    }

    /**
     * @return Active
     */
    public function findAnmeldeSchuljahrFromCity($stadt):?Active
    {
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
    }

    public function findSchuleBetweentwoDates(\DateTime $von, \DateTime $bis, Stadt $stadt):?Active
    {
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
    }

    /**
     * @return Active
     */
    public function findSchuljahrFromCity(Stadt $stadt, \DateTime $today):?Active
    {
        $qb = $this->createQueryBuilder('a');
        $qb->andWhere('a.stadt = :stadt')
            ->andWhere('a.anmeldeStart <= :today')
            ->andWhere('a.bis >= :today')
            ->orderBy('a.bis', 'DESC')
            ->setMaxResults(1)
            ->setParameter('today', $today)
            ->setParameter('stadt', $stadt);
        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * @return Active[] Returns an array of Active objects
     */

    public function findFutureSchuljahreByCity(Stadt $stadt)
    {
        $now = new \DateTime();
        $now->setTime(23,59);
        $qb = $this->createQueryBuilder('a');
        $qb->andWhere('a.stadt = :val')
            ->setParameter('val', $stadt)
            ->andWhere($qb->expr()->gt('a.bis',':now'))
            ->setParameter('now',$now)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10);

        return $qb->getQuery()
            ->getResult();
    }

}
