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
}
