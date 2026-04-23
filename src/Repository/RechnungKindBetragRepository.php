<?php

namespace App\Repository;

use App\Entity\RechnungKindBetrag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RechnungKindBetrag>
 *
 * @method RechnungKindBetrag|null find($id, $lockMode = null, $lockVersion = null)
 * @method RechnungKindBetrag|null findOneBy(array $criteria, array $orderBy = null)
 * @method RechnungKindBetrag[]    findAll()
 * @method RechnungKindBetrag[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RechnungKindBetragRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RechnungKindBetrag::class);
    }

    public function save(RechnungKindBetrag $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(RechnungKindBetrag $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return RechnungKindBetrag[] Returns an array of RechnungKindBetrag objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('r.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?RechnungKindBetrag
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
