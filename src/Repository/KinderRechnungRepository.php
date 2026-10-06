<?php

namespace App\Repository;

use App\Entity\KinderRechnung;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<KinderRechnung>
 *
 * @method KinderRechnung|null find($id, $lockMode = null, $lockVersion = null)
 * @method KinderRechnung|null findOneBy(array $criteria, array $orderBy = null)
 * @method KinderRechnung[]    findAll()
 * @method KinderRechnung[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class KinderRechnungRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, KinderRechnung::class);
    }

//    /**
//     * @return KinderRechnung[] Returns an array of KinderRechnung objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('k')
//            ->andWhere('k.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('k.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?KinderRechnung
//    {
//        return $this->createQueryBuilder('k')
//            ->andWhere('k.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
