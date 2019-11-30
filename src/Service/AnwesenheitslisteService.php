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


    public function __construct( EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;

    }

    public
    function anwesenheitsListe(\DateTime $selectDate,Organisation $organisation)
    {


        $qb = $this->em->getRepository(Kind::class)->createQueryBuilder('k');
        $qb->innerJoin('k.kindFerienblocks', 'kindFerienblocks')
            ->andWhere($qb->expr()->andX($qb->expr()->gte('kindFerienblocks.state', ':stateGebucht'),
                $qb->expr()->lt('kindFerienblocks.state', ':stateStorniert')))
            ->innerJoin('kindFerienblocks.ferienblock', 'ferienblock')
            ->andWhere($qb->expr()->lte('ferienblock.endDate', ':date'))
            ->andWhere($qb->expr()->gte('ferienblock.startDate', ':date'))
            ->andWhere('ferienblock.organisation = :organisation')
            ->setParameter('date', $selectDate)
            ->setParameter('organisation', $organisation)
            ->setParameter('stateGebucht', 10)
            ->setParameter('stateStorniert', 20);
        $query = $qb->getQuery();
       return $query->getResult();
    }

}
