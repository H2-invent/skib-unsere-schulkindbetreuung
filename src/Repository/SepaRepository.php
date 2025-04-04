<?php

namespace App\Repository;

use App\Entity\Organisation;
use App\Entity\Sepa;
use App\Entity\Stammdaten;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use function Doctrine\ORM\QueryBuilder;

/**
 * @method Sepa|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sepa|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sepa[]    findAll()
 * @method Sepa[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SepaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sepa::class);
    }

    // /**
    //  * @return Sepa[] Returns an array of Sepa objects
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
    public function findOneBySomeField($value): ?Sepa
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */


     /**
      * @return Sepa[] Returns an array of Sepa objects
     */
    public function findSepaBetweenTwoDates(\DateTime $von, \DateTime $bis, Organisation $organisation)
    {
        // automatically knows to select Products
        // the "p" is an alias you'll use in the rest of the query

        $qb = $this->createQueryBuilder('s');

        $query = $qb->andWhere('s.organisation = :org')
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->between(
                        's.bis',
                        ':von',
                        ':bis'
                    ),
                    $qb->expr()->orX(
                        $qb->expr()->between(
                            's.von',
                            ':von',
                            ':bis'
                        ),
                        $qb->expr()->orX(
                            $qb->expr()->andX(
                                $qb->expr()->between(
                                    's.von',
                                    ':von',
                                    ':bis'
                                ),
                                $qb->expr()->between(
                                    's.bis',
                                    ':von',
                                    ':bis'
                                )
                            )
                        ),

                            $qb->expr()->andX(
                                $qb->expr()->gte(
                                    's.bis',
                                    ':bis'

                                ),
                                $qb->expr()->lte(
                                    's.von',
                                    ':von'
                                )


                        )
                        )
                    )
                )
            ->setParameter('von', $von)
            ->setParameter('bis',$bis)
            ->setParameter('org', $organisation)
            ->setMaxResults(10)
            ->getQuery();


        return $query->getResult();

        // to get just one result:
        // $product = ;
    }

     /**
      * @return Sepa[] Returns an array of Sepa objects
      */
    public function findOtherSepaBySepaAndStammdaten(Stammdaten $stammdaten, Sepa $sepa)
    {
        return $this->createQueryBuilder('s')
            ->innerJoin('s.rechnungen', 'rechnungen')
            ->innerJoin('s.organisation', 'organisation')
            ->innerJoin('rechnungen.stammdaten','stammdaten')
            ->andWhere('stammdaten = :stammdaten')->setParameter('stammdaten',$stammdaten)
            ->andWhere('organisation = :organisation')->setParameter('organisation', $sepa->getOrganisation())
            ->getQuery()
            ->getResult()
        ;
    }


}
