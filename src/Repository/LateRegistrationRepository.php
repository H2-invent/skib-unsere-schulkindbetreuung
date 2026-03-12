<?php

namespace App\Repository;

use App\Entity\LateRegistration;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

/**
 * @extends ServiceEntityRepository<LateRegistration>
 *
 * @method LateRegistration|null find($id, $lockMode = null, $lockVersion = null)
 * @method LateRegistration|null findOneBy(array $criteria, array $orderBy = null)
 * @method LateRegistration[]    findAll()
 * @method LateRegistration[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LateRegistrationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LateRegistration::class);
    }

    public function findByStringToken(string $stringToken): ?LateRegistration
    {
        $token = Uuid::v7()::fromRfc4122($stringToken);

        return $this->createQueryBuilder('late')
            ->innerJoin('late.schuljahr', 'schuljahr')
            ->innerJoin('late.stadt', 'stadt')
            ->andWhere('late.token = :token')->setParameter('token', $token)
            ->getQuery()
            ->getResult()
        ;
    }
}
