<?php

namespace App\Service;

use App\Entity\KindFerienblock;
use App\Entity\Organisation;
use Doctrine\ORM\EntityManagerInterface;

// <- Add this

class AnwesenheitslisteService
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {
    }

    public function anwesenheitsListe(\DateTime $selectDate, Organisation $organisation)
    {
        $qb = $this->em->getRepository(KindFerienblock::class)->createQueryBuilder('k');

        $qb->andWhere($qb->expr()->andX($qb->expr()->gte('k.state', ':stateGebucht'),
            $qb->expr()->lt('k.state', ':stateStorniert')))
            ->innerJoin('k.ferienblock', 'ferienblock')
            ->innerJoin('k.kind', 'kind')
            ->andWhere($qb->expr()->gte('ferienblock.endDate', ':date'))
            ->andWhere($qb->expr()->lte('ferienblock.startDate', ':date'))
            ->andWhere('ferienblock.organisation = :organisation')
            ->andWhere('kind.fin = 1')
            ->setParameter('date', $selectDate)
            ->setParameter('organisation', $organisation)
            ->setParameter('stateGebucht', 10)
            ->setParameter('stateStorniert', 20);
        $query = $qb->getQuery();

        return $query->getResult();
    }
}
