<?php

namespace App\Repository;

use App\Entity\AutoBlockAssignment;
use App\Entity\AutoBlockAssignmentChild;
use App\Entity\Organisation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AutoBlockAssignmentChild>
 *
 * @method AutoBlockAssignmentChild|null find($id, $lockMode = null, $lockVersion = null)
 * @method AutoBlockAssignmentChild|null findOneBy(array $criteria, array $orderBy = null)
 * @method AutoBlockAssignmentChild[]    findAll()
 * @method AutoBlockAssignmentChild[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AutoBlockAssignmentChildRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AutoBlockAssignmentChild::class);
    }

    /**
     * @return AutoBlockAssignmentChild[]
     */
    public function findByAutoBlockAssignmentWeighted(AutoBlockAssignment $autoBlockAssignment): array
    {
        return $this->createQueryBuilder('child')
            ->where('child.autoBlockAssignment = :autoBlockAssignment')
            ->setParameter('autoBlockAssignment', $autoBlockAssignment)
            ->orderBy('child.weight', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByOrganisationAddZeitblocksCounts(Organisation $organisation): array
    {
        return $this->createQueryBuilder('child')
            ->select('child', 'SUM(zeitblock.accepted) as countAccepted', 'SUM(zeitblock.warteschlange) as countWarteschlange')
            ->innerJoin('child.autoBlockAssignment', 'autoblock')
            ->innerJoin('child.kind', 'kind')
            ->innerJoin('child.zeitblocks', 'zeitblock')
            ->where('autoblock.organisation = :organisation')
            ->setParameter('organisation', $organisation)
            ->groupBy('child.id')
            ->orderBy('countAccepted', 'DESC')
            ->getQuery()
            ->getResult();
    }

//    /**
//     * @return AutoBlockAssignmentChild[] Returns an array of AutoBlockAssignmentChild objects
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

//    public function findOneBySomeField($value): ?AutoBlockAssignmentChild
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
