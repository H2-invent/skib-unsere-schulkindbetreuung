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
            ->select(
                'child',
                'SUM(zeitblock.accepted) as countAccepted',
                'SUM(zeitblock.warteschlange) as countWarteschlange',
                '(SELECT COUNT(file2.id) FROM App\Entity\Stammdaten eltern2 JOIN eltern2.file file2 WHERE kind MEMBER OF eltern2.kinds) as countFile'
            )
            ->innerJoin('child.autoBlockAssignment', 'autoblock')
            ->innerJoin('child.kind', 'kind')
            ->innerJoin('child.zeitblocks', 'zeitblock')
            ->where('autoblock.organisation = :organisation')
            ->setParameter('organisation', $organisation)
            ->groupBy('child.id')
            ->orderBy('countAccepted', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }
}
