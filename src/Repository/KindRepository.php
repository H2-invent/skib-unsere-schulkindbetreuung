<?php

namespace App\Repository;

use App\Entity\Active;
use App\Entity\Kind;
use App\Entity\Organisation;
use App\Entity\Stammdaten;
use App\Entity\Zeitblock;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Kind|null find($id, $lockMode = null, $lockVersion = null)
 * @method Kind|null findOneBy(array $criteria, array $orderBy = null)
 * @method Kind[]    findAll()
 * @method Kind[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class KindRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Kind::class);
    }

    public function findBeworbenByZeitblock(Zeitblock $zeitblock)
    {
        return $this->createQueryBuilder('k')
            ->innerJoin('k.beworben', 'beworben')
            ->innerJoin('k.eltern', 'eltern')
            ->andWhere('beworben = :beworben')
            ->setParameter('beworben', $zeitblock)
            ->andWhere('k.startDate is not NULL')
            ->andWhere('eltern.created_at is not NULL')

            ->getQuery()
            ->getResult();
    }
    public function findActualWorkingCopybyKind(Kind $kind): ?Kind
    {
        return $this->createQueryBuilder('k')
            ->innerJoin('k.eltern', 'eltern')
            ->andWhere('eltern.created_at is NULL')
            ->andWhere('k.tracing = :tracingId')
            ->setParameter('tracingId', $kind->getTracing())
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return Kind[] Returns an array of Kind objects
     */

    public function findHistoryOfThisChild(Kind $kind)
    {
        return $this->createQueryBuilder('k')
            ->innerJoin('k.eltern', 'eltern')
            ->andWhere('k.tracing = :tracing')
            ->andWhere('k.startDate is not NULL')
            ->andWhere('eltern.created_at is not null')
            ->setParameter('tracing', $kind->getTracing())
            ->addOrderBy('k.startDate', 'ASC')
            ->addOrderBy('eltern.created_at', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Kind[] Returns an array of Kind objects
     */
    public function findAllChildrenWithHistoryFromParent(Stammdaten $stammdaten)
    {
        return $this->createQueryBuilder('k')
            ->andWhere('k.startDate is not NULL ')
            ->innerJoin('k.eltern', 'eltern')
            ->andWhere('eltern.created_at IS NOT NULL')
            ->andWhere('eltern.tracing =:tracing')->setParameter('tracing', $stammdaten->getTracing())
            ->orderBy('k.startDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findLatestKindForDate(Kind $kind, \DateTime $dateTime, $demo = false): ?Kind
    {
        $qb = $this->createQueryBuilder('k')
            ->andWhere('k.tracing = :tracing')->setParameter('tracing', $kind->getTracing())
            ->innerJoin('k.eltern', 'eltern');
        if (!$demo) {
            $qb->andWhere('eltern.created_at IS NOT NULL');
        }

        $kind = $qb->andWhere('k.startDate <= :now')->setParameter('now', $dateTime)
            ->andWhere('k.startDate is NOT NULL')
            ->orderBy('k.startDate', 'DESC')
            ->addOrderBy('eltern.created_at','DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
        return $kind;

    }

    public function findLatestKindforKind(Kind $kind): ?Kind
    {
        $kinder = $this->createQueryBuilder('k')
            ->andWhere('k.tracing = :tracing')->setParameter('tracing', $kind->getTracing())
            ->innerJoin('k.eltern', 'eltern')
            ->andWhere('eltern.created_at IS NOT NULL')
            ->andWhere('k.startDate IS NOT NULL')
            ->orderBy('k.startDate', 'ASC')
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult();
        return $kinder;
    }

    /**
     * @return Kind[] Returns an array of Kind objects
     */
    public function findKinderProStammdatenAnStichtag(Stammdaten $stammdaten, \DateTime $dateTime, $demo = false)
    {
        $qb = $this->createQueryBuilder('k')
            ->innerJoin('k.eltern', 'eltern')
            ->andWhere('eltern.tracing = :tracing')->setParameter('tracing', $stammdaten->getTracing());
        if (!$demo) {
            $qb->andWhere('eltern.created_at IS NOT NULL');
        }

        $kinderHistory = $qb->andWhere('k.startDate <= :now')->setParameter('now', $dateTime)
            ->andWhere('k.startDate is NOT NULL')
            ->orderBy('k.startDate', 'ASC')
            ->orderBy('eltern.created_at', 'DESC')
            ->getQuery()
            ->getResult();
        $kinder = array();

        foreach ($kinderHistory as $data) {
            if (array_key_exists($data->getTracing(), $kinder)) {
                if ($data->getStartDate() > $kinder[$data->getTracing()]->getStartDate()) {
                    $kinder[$data->getTracing()] = $data;
                }
            } else {
                $kinder[$data->getTracing()] = $data;
            }
        }
        return $kinder;
    }

    public function findSampleKind(): ?Kind
    {
        return $this->createQueryBuilder('k')
            ->innerJoin('k.eltern', 'eltern')
            ->innerJoin('k.schule', 'schule')
            ->innerJoin('schule.organisation', 'orga')
            ->innerJoin('schule.stadt', 'stadt')
            ->orderBy('k.id', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * @return Kind[]
     */
    public function findChildHistoryForParentAndSchoolyear(string $email, Active $schuljahr): array
    {
        $tracingRows = $this->createQueryBuilder('kind')
            ->select('DISTINCT kind.tracing AS tracing')
            ->innerJoin('kind.eltern', 'eltern')
            ->leftJoin('kind.zeitblocks', 'zeitblock')
            ->leftJoin('kind.beworben', 'beworben')
            ->andWhere('eltern.email = :email')->setParameter('email', $email)
            ->andWhere('eltern.created_at IS NOT NULL')
            ->andWhere('kind.startDate IS NOT NULL')
            ->andWhere('(zeitblock.active = :schuljahr OR beworben.active = :schuljahr)')
            ->setParameter('schuljahr', $schuljahr)
            ->getQuery()
            ->getScalarResult();

        $tracings = array_values(array_filter(array_map(static function (array $row) {
            return $row['tracing'] ?? null;
        }, $tracingRows)));

        if (count($tracings) === 0) {
            return [];
        }

        return $this->createQueryBuilder('kind')
            ->innerJoin('kind.eltern', 'eltern')
            ->andWhere('kind.tracing IN (:tracings)')->setParameter('tracings', $tracings)
            ->andWhere('eltern.created_at IS NOT NULL')
            ->andWhere('kind.startDate IS NOT NULL')
            ->orderBy('kind.tracing', 'ASC')
            ->addOrderBy('kind.startDate', 'ASC')
            ->addOrderBy('kind.history', 'ASC')
            ->addOrderBy('eltern.created_at', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Kind[]
     */
    public function findKindWithBeworbenZeitblocksForSchuljahr(Organisation $organisation, Active $schuljahr): array
    {
        $subQuery = $this->createQueryBuilder('kind2')
            ->select('MAX(eltern2.created_at)')
            ->innerJoin('kind2.eltern', 'eltern2')
            ->where('kind2.tracing = kind.tracing')
            ->andWhere('eltern2.created_at IS NOT NULL')
            ->getDQL()
        ;

        return $this->createQueryBuilder('kind')
            ->innerJoin('kind.beworben', 'beworben_zeitblock')
            ->innerJoin('kind.eltern', 'eltern')
            ->innerJoin('beworben_zeitblock.active', 'active')
            ->innerJoin('beworben_zeitblock.schule', 'schule')
            ->innerJoin('schule.organisation', 'organisation')
            ->andWhere('active = :active')->setParameter('active', $schuljahr)
            ->andWhere('kind.startDate is not NULL')
            ->andWhere('eltern.created_at is not NULL')
            ->andWhere('beworben_zeitblock.deleted = 0')
            ->andWhere('organisation = :organisation')->setParameter('organisation', $organisation)
            ->andWhere('eltern.created_at = (' .$subQuery. ')')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findAutoBlockAssignedKindByZeitblock(Zeitblock $zeitblock): array
    {
        return $this->createQueryBuilder('kind')
            ->innerJoin('kind.autoBlockAssignmentChild', 'child')
            ->innerJoin('child.zeitblocks', 'child_zeitblock')
            ->innerJoin('child_zeitblock.zeitblock', 'zeitblock')
            ->andWhere('zeitblock = :zeitblock')
            ->andWhere('child_zeitblock.accepted = 1')
            ->setParameter('zeitblock', $zeitblock)
            ->getQuery()
            ->getResult()
        ;
    }
}
