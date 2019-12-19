<?php

namespace App\Service;

use App\Entity\Kind;
use App\Entity\KindFerienblock;
use App\Entity\Organisation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;


// <- Add this

class AnwesenheitslisteService
{


    private $em;


    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;

    }

    public
    function anwesenheitsListe(\DateTime $selectDate, Organisation $organisation)
    {
        $qb = $this->em->getRepository(KindFerienblock::class)->createQueryBuilder('k');

        $qb->andWhere($qb->expr()->andX($qb->expr()->gte('k.state', ':stateGebucht'),
            $qb->expr()->lt('k.state', ':stateStorniert')))
            ->innerJoin('k.ferienblock', 'ferienblock')
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
