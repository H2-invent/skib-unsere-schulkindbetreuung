<?php

namespace App\Repository;

use App\Entity\StammdatenFerien;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<StammdatenFerien>
 *
 * @method StammdatenFerien|null find($id, $lockMode = null, $lockVersion = null)
 * @method StammdatenFerien|null findOneBy(array $criteria, array $orderBy = null)
 * @method StammdatenFerien[]    findAll()
 * @method StammdatenFerien[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StammdatenFerienRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StammdatenFerien::class);
    }

    public function save(StammdatenFerien $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(StammdatenFerien $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    //    /**
    //     * @return StammdatenFerien[] Returns an array of StammdatenFerien objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('s.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?StammdatenFerien
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
