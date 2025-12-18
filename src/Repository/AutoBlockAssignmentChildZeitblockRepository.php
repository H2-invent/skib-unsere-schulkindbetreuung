<?php

namespace App\Repository;

use App\Entity\AutoBlockAssignmentChildZeitblock;
use App\Entity\Organisation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AutoBlockAssignmentChildZeitblock>
 *
 * @method AutoBlockAssignmentChildZeitblock|null find($id, $lockMode = null, $lockVersion = null)
 * @method AutoBlockAssignmentChildZeitblock|null findOneBy(array $criteria, array $orderBy = null)
 * @method AutoBlockAssignmentChildZeitblock[]    findAll()
 * @method AutoBlockAssignmentChildZeitblock[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AutoBlockAssignmentChildZeitblockRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AutoBlockAssignmentChildZeitblock::class);
    }

    /**
     * @return AutoBlockAssignmentChildZeitblock[]
     */
    public function findAcceptedJoinChildAndKindByOrganisation(Organisation $organisation): array
    {
        return $this->createQueryBuilder('z')
            ->innerJoin('z.child', 'child')
            ->innerJoin('child.autoBlockAssignment', 'auto')
            ->innerJoin('z.zeitblock', 'block')
            ->innerJoin('child.kind', 'kind')
            ->where('z.accepted = 1')
            ->andWhere('auto.organisation = :organisation')
            ->setParameter('organisation', $organisation)
            ->getQuery()
            ->getResult()
        ;
    }

//    /**
//     * @return AutoBlockAssignmentChildZeitblock[] Returns an array of AutoBlockAssignmentChildZeitblock objects
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

//    public function findOneBySomeField($value): ?AutoBlockAssignmentChildZeitblock
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
