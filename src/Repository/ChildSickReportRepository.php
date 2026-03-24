<?php

namespace App\Repository;

use App\Entity\ChildSickReport;
use App\Entity\Kind;
use App\Entity\Organisation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ChildSickReportRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ChildSickReport::class);
    }

    public function findLatestForChild(Kind $kind): ?ChildSickReport
    {
        return $this->createQueryBuilder('report')
            ->andWhere('report.kind = :kind')->setParameter('kind', $kind)
            ->orderBy('report.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /** @return ChildSickReport[] */
    public function findForTodayByOrganisation(Organisation $organisation): array
    {
        $todayStart = (new \DateTime())->setTime(0, 0, 0);
        $todayEnd = (new \DateTime())->setTime(23, 59, 59);

        return $this->createQueryBuilder('report')
            ->innerJoin('report.kind', 'kind')
            ->innerJoin('kind.schule', 'schule')
            ->innerJoin('schule.organisation', 'organisation')
            ->andWhere('organisation = :organisation')->setParameter('organisation', $organisation)
            ->andWhere('report.von <= :todayEnd')->setParameter('todayEnd', $todayEnd)
            ->andWhere('report.bis >= :todayStart')->setParameter('todayStart', $todayStart)
            ->orderBy('kind.nachname', 'ASC')
            ->addOrderBy('kind.vorname', 'ASC')
            ->addOrderBy('report.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
