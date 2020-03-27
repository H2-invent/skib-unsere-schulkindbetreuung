<?php

namespace App\Service;

use App\Entity\Anwesenheit;
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
        $result['errorText'] = 'Hallo ' . $kind->getVorname() . ', Willkommen in der Schulkindbetreuung.';
        $result['checkinText'] = $this->translator->trans('Eingecheckt');
        $result['name'] = 'Name: ' . $kind->getVorname() . ' ' . $kind->getNachname();
        $result['kurs'] = 'SKiB | ' . $organisation->getName() . ' | ' . $kind->getSchule()->getName();
        $block = $this->getZeitblock($dateTime, $kind, $organisation);

        if (sizeof($block) == 0) {
            $result['error'] = true;
            $result['errorText'] = $this->translator->trans('Das Kind ist aktuell zu keiner Schulkindbetreuung angemeldet');
            $result['checkinText'] = $this->translator->trans('Nicht eingecheckt');

        } else {
            $this->getAnwesenheitToday($kind, $dateTime, $organisation);
        }

        return $result;
    }


    private function getZeitblock(\DateTime $dateTime, Kind $kind, Organisation $organisation)
    {
        $wochentag = $dateTime->format('N') - 1;
        $timeLate = clone $dateTime;
        $timeLate->modify('+1 hour');
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

    public function getAnwesenheitToday(Kind $kind, \DateTime $dateTime, Organisation $organisation)
    {

        $midnight = clone $dateTime;

        $midnight->setTime(0, 0, 0);
        $qb = $this->em->getRepository(Anwesenheit::class)->createQueryBuilder('an');
        $qb->andWhere('an.kind = :kind')
            ->andWhere(
                $qb->expr()->between('an.arrivedAt', ':midnight', ':now')
            )
            ->setParameter('midnight', $midnight)
            ->setParameter('now', $dateTime)
            ->setParameter('kind', $kind);
        $query = $qb->getQuery();
        $anwesenheit = $query->getOneOrNullResult();

        if ($anwesenheit) {
            $anwesenheit->setArrivedAt($dateTime);
        } else {
            $anwesenheit = new Anwesenheit();
            $anwesenheit->setCreatedAt($dateTime);
            $anwesenheit->setArrivedAt($dateTime);
            $anwesenheit->setKind($kind);
            $anwesenheit->setOrganisation($organisation);
        }
        $this->em->persist($anwesenheit);
        $this->em->flush();

        return $anwesenheit;
    }

    public function getAllKidsToday(Organisation $organisation, \DateTime $dateTime)
    {

        $midnight = clone $dateTime;
        $midnight->setTime(0, 0, 0);
        $dateTime->setTime(23, 59, 59);
        $qb = $this->em->getRepository(Kind::class)->createQueryBuilder('k');
        $qb->innerJoin('k.anwesenheitenSchulkindbetreuung','an')
        ->andWhere('an.organisation = :org')
            ->andWhere(
                $qb->expr()->between('an.arrivedAt', ':midnight', ':endDay')
            )
            ->setParameter('midnight', $midnight)
            ->setParameter('endDay', $dateTime)
            ->setParameter('org', $organisation);
        $query = $qb->getQuery();
        return $query->getResult();
    }
}
