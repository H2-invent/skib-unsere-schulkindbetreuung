<?php

namespace App\Service;

use App\Entity\Kind;
use App\Entity\Organisation;
use App\Entity\Zeitblock;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use function Doctrine\ORM\QueryBuilder;


// <- Add this

class CheckinSchulkindservice
{


    private $em;
    private $translator;

    public function __construct(TranslatorInterface $translator, EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
        $this->translator = $translator;
    }

    public
    function checkin(Kind $kind, \DateTime $dateTime, Organisation $organisation)
    {
        $result['error'] = false;
        $result['errorText'] = $this->translator->trans('Kind erfolgreich eingecheckt');
        $result['checkinText'] = $this->translator->trans('Eingecheckt');
        $result['vorname'] = $kind->getVorname();
        $result['nachname'] = $kind->getNachname();
        $result['kurs'] = 'SKiB | ' . $organisation->getName();
        $block = $this->getZeitblock($dateTime, $kind, $organisation);

        if (sizeof($block) == 0) {
            $result['error'] = false;
            $result['errorText'] = $this->translator->trans('Das Kind ist aktuell zu keiner Schulkindbetreuung angemeldet');
            $result['checkinText'] = $this->translator->trans('Nicht eingecheckt');

        }

        return $result;
    }


    private function getZeitblock(\DateTime $dateTime, Kind $kind, Organisation $organisation)
    {
        $wochentag = $dateTime->format('N') - 1;
        $timeLate = clone $dateTime;
        $timeLate->modify('+1 hour');
        dump($timeLate);
        $qb = $this->em->getRepository(Zeitblock::class)->createQueryBuilder('zb');
        $qb->innerJoin('zb.active', 'active')
            ->andWhere(
                $qb->expr()->andX(
                    $qb->expr()->lte('active.von', ':date'),
                    $qb->expr()->gte('active.bis', ':date')
                )
            )
            ->innerJoin('zb.schule', 'schule')
            ->andWhere('schule.organisation =:org')
            ->andWhere('zb.wochentag =:wochentag')
            ->innerJoin('zb.kind', 'kind')
            ->andWhere('kind =:kind')
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->andX(
                        $qb->expr()->lte('zb.von', ':time'),
                        $qb->expr()->gte('zb.bis', ':time')
                    ),
                    $qb->expr()->between('zb.von', ':time', ':timeLate')
                )

            )
            ->setParameter('org', $organisation)
            ->setParameter('kind', $kind)
            ->setParameter('date', $dateTime)
            ->setParameter('wochentag', $wochentag)
            ->setParameter('time', $dateTime)
            ->setParameter('timeLate', $timeLate);

        $query = $qb->getQuery();
        $block = $query->getResult();

        return $block;
    }
}
