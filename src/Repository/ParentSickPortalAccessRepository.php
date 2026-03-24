<?php

namespace App\Repository;

use App\Entity\ParentSickPortalAccess;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

class ParentSickPortalAccessRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ParentSickPortalAccess::class);
    }

    public function findByStringToken(string $stringToken): ?ParentSickPortalAccess
    {
        $token = Uuid::v7()::fromRfc4122($stringToken);

        return $this->createQueryBuilder('access')
            ->innerJoin('access.schuljahr', 'schuljahr')
            ->innerJoin('access.stadt', 'stadt')
            ->andWhere('access.token = :token')->setParameter('token', $token)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
