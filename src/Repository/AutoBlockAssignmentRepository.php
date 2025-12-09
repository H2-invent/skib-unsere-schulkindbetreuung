<?php

namespace App\Repository;

use App\Entity\AutoBlockAssignment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AutoBlockAssignment>
 *
 * @method AutoBlockAssignment|null find($id, $lockMode = null, $lockVersion = null)
 * @method AutoBlockAssignment|null findOneBy(array $criteria, array $orderBy = null)
 * @method AutoBlockAssignment[]    findAll()
 * @method AutoBlockAssignment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AutoBlockAssignmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AutoBlockAssignment::class);
    }

    public function save(AutoBlockAssignment $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(AutoBlockAssignment $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return AutoBlockAssignment[] Returns an array of AutoBlockAssignment objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?AutoBlockAssignment
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
