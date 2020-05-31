<?php

namespace App\Repository;

use App\Entity\EmailResponse;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method EmailResponse|null find($id, $lockMode = null, $lockVersion = null)
 * @method EmailResponse|null findOneBy(array $criteria, array $orderBy = null)
 * @method EmailResponse[]    findAll()
 * @method EmailResponse[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EmailResponseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EmailResponse::class);
    }

    // /**
    //  * @return EmailResponse[] Returns an array of EmailResponse objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?EmailResponse
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
