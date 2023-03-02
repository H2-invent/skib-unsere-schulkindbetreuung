<?php

namespace App\Repository;

use App\Entity\Active;
use App\Entity\Kind;
use App\Entity\Stadt;
use App\Entity\Stammdaten;
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
    public function findActiveSchuljahrFromCity(Stadt $stadt): ?Active
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
      * @return Active[] Returns an array of Active objects
      */
    public function findLaufendeSchuljahreFromCity(Stadt $stadt)
    {
        $today = new \DateTime();
        $qb = $this->createQueryBuilder('a')
            ->andWhere('a.stadt = :stadt')
            ->andWhere('a.von <= :today')
            ->andWhere('a.bis >= :today')
            ->setParameter('today', $today)
            ->setParameter('stadt', $stadt)
            ->orderBy('a.von', 'ASC')
            ->getQuery();


        return $qb->getResult();

    }

    /**
     * @return Active[] Returns an array of Active objects
     */
    public function findAnmeldeSchuljahreFromCity(Stadt $stadt)
    {
        $today = new \DateTime();
        $qb = $this->createQueryBuilder('a')
            ->andWhere('a.stadt = :stadt')
            ->andWhere('a.anmeldeStart <= :today')
            ->andWhere('a.anmeldeEnde >= :today')
            ->setParameter('today', $today)
            ->setParameter('stadt', $stadt)
            ->orderBy('a.von', 'ASC')
            ->getQuery();


        return $qb->getResult();

    }

    /**
     * @return Active
     */
    public function findSchuljahrFromStamdaten(Stammdaten $stammdaten): ?Active
    {
        $qb = $this->createQueryBuilder('a');
        $qb->innerJoin('a.blocks', 'blocks')
            ->innerJoin('blocks.kind', 'kind')
            ->innerJoin('kind.eltern', 'eltern')
            ->leftJoin('blocks.kinderBeworben', 'kinderBeworben')
            ->leftJoin('kinderBeworben.eltern', 'elternBeworben')
            ->orWhere(
                $qb->expr()->orX(
                    'eltern.tracing = :eltern', 'elternBeworben.tracing =:eltern'
                )
            )
            ->setParameter('eltern', $stammdaten->getTracing())
            ->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }


    /**
     * @return Active
     */
    public function findSchuljahrFromKind(Kind $kind): ?Active
    {
        $qb = $this->createQueryBuilder('a');
        $qb->innerJoin('a.blocks', 'blocks')
            ->leftJoin('blocks.kind', 'kind')
            ->leftJoin('blocks.kinderBeworben', 'kinderBeworben')
            ->andWhere($qb->expr()->orX(
                $qb->expr()->eq('kind.tracing', ':tracing1'),
                $qb->expr()->eq('kinderBeworben.tracing', ':tracing2')
            ))
            ->setParameter('tracing1', $kind->getTracing())
            ->setParameter('tracing2', $kind->getTracing())
            ->setMaxResults(1);
        return $qb->getQuery()->getOneOrNullResult();
    }

//SELECT * FROM active INNER JOIN zeitblock ON active.id=zeitblock.active_id INNER JOIN zeitblock_kind ON zeitblock.id=zeitblock_kind.zeitblock_id INNER JOIN kind k4_ ON k4_.id=zeitblock_kind.kind_id INNER JOIN kind_zeitblock ON zeitblock.id=zeitblock_kind.zeitblock_id INNER JOIN kind k5_ ON k5_.id=zeitblock_kind.kind_id WHERE (k4_.tracing="a6e42d355167a63492c122056132d664" OR k5_.tracing="a6e42d355167a63492c122056132d664") LIMIT 1;

    /**
     * @return Active
     */
    public function findAnmeldeSchuljahrFromCity($stadt): ?Active
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

    /**
     * @return Active
     */
    public function findSchuljahrfromStadtAndStichtag(Stadt  $stadt, \DateTime $stichtag): ?Active
    {
        $qb = $this->createQueryBuilder('a')
            ->andWhere('a.stadt = :stadt')
            ->andWhere('a.von <= :stichtag')
            ->andWhere('a.bis >= :stichtag')
            ->setParameter('stichtag', $stichtag)
            ->setParameter('stadt', $stadt)
            ->getQuery()
            ->setMaxResults(1);

        return $qb->getOneOrNullResult();
    }

    public function findSchuljahrBetweentwoDates(\DateTime $von, \DateTime $bis, Stadt $stadt): ?Active
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
    public function findSchuljahrFromCity(Stadt $stadt, \DateTime $today): ?Active
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
        $now->setTime(23, 59);
        $qb = $this->createQueryBuilder('a');
        $qb->andWhere('a.stadt = :val')
            ->setParameter('val', $stadt)
            ->andWhere($qb->expr()->gt('a.bis', ':now'))
            ->setParameter('now', $now)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10);

        return $qb->getQuery()
            ->getResult();
    }

}
