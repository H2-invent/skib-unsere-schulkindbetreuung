<?php

namespace App\Repository;

use App\Entity\Ferienblock;
use App\Entity\Stadt;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

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

    /**
     * Returns future holiday programmes whose booking period has not ended yet.
     *
     * @return Ferienblock[]
     */
    public function findUpcomingBookableForCity(Stadt $stadt, \DateTimeInterface $today): array
    {
        return $this->createQueryBuilder('ferienblock')
            ->leftJoin(
                'ferienblock.kindFerienblocks',
                'booking',
                'WITH',
                'booking.state = :bookedState',
            )
            ->leftJoin('booking.kind', 'bookedChild', 'WITH', 'bookedChild.fin = :activeChild')
            ->andWhere('ferienblock.stadt = :stadt')
            ->andWhere('ferienblock.startDate >= :today')
            ->andWhere('ferienblock.endVerkauf >= :today')
            ->groupBy('ferienblock.id')
            ->having('ferienblock.maxAnzahl IS NULL OR COUNT(bookedChild.id) < ferienblock.maxAnzahl')
            ->setParameter('stadt', $stadt)
            ->setParameter('today', $today)
            ->setParameter('bookedState', 10)
            ->setParameter('activeChild', true)
            ->orderBy('ferienblock.startDate', 'ASC')
            ->addOrderBy('ferienblock.StartTime', 'ASC')
            ->getQuery()
            ->getResult();
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
    public function findFerienblocksFromToday($stadt, \DateTime $start = null, \DateTime $end = null, $tag = array(),$freeSpace = null)
    {
        // automatically knows to select Products
        // the "p" is an alias you'll use in the rest of the query
        $today = new \DateTime();
        $qb = $this->createQueryBuilder('f');

        if ($start !== null) {
            $qb->andWhere($qb->expr()->gte('f.startDate',':start'))
                ->setParameter('start', $start);
        }
        if ($end !== null) {
            $qb->andWhere($qb->expr()->lte('f.endDate',':end'))
                ->setParameter('end', $end);
        }
        if ($end === null && $start === null) {
            $qb->
            andWhere('f.endDate >= :today')
                ->setParameter('today', $today);
        }

        if(sizeof($tag)> 0){
            $qb->innerJoin('f.kategorie','kateg')
                ->andWhere('kateg IN (:kat)')
                ->setParameter('kat', $tag);


        }

        $qb->
        andWhere('f.stadt = :stadt')
            ->addOrderBy('f.startDate', 'asc')
            ->setParameter('stadt', $stadt);

        $ferien = $qb->getQuery()->getResult();
        $res = array();
        foreach ($ferien as $data) {
            if($freeSpace !== null && $freeSpace === true){
                if($data->getMaxAnzahl() - sizeof($data->getKindFerienblocksGebucht())> 0){
                    $res[$data->getStartDate()->format('d.m.Y')][] = $data;
                }
            }else{
                $res[$data->getStartDate()->format('d.m.Y')][] = $data;
            }

        }
        return $res;
        // to get just one result:
        // $product = ;
    }
}
