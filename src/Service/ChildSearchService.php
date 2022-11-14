<?php

namespace App\Service;

use App\Entity\Active;
use App\Entity\Anwesenheit;
use App\Entity\Kind;
use App\Entity\Organisation;
use App\Entity\Schule;
use App\Entity\User;
use App\Entity\Zeitblock;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use function Doctrine\ORM\QueryBuilder;


// <- Add this

class ChildSearchService
{


    private $em;
    private $translator;

    public function __construct(TranslatorInterface $translator, EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
        $this->translator = $translator;
    }

    public function searchChild($parameters, Organisation $organisation, $isApp, User $user)
    {

        $qb = $this->em->getRepository(Kind::class)->createQueryBuilder('k')
            ->innerJoin('k.zeitblocks', 'b');
        //Schule als FIlter ausgewählt
        if (isset($parameters['schule']) && $parameters['schule'] !== "") {

            $schule = $this->em->getRepository(Schule::class)->find($parameters['schule']);
            $qb->andWhere('b.schule = :schule')
                ->setParameter('schule', $schule);
        } else {
            $orXSchule = $qb->expr()->orX();
            $schulen = sizeof($user->getSchulen())=== 0?$organisation->getSchule():$user->getSchulen();
            foreach ($schulen as $data) {
                $orXSchule->add('b.schule =:schule' . $data->getId());
                $qb->setParameter('schule' . $data->getId(), $data);

            }
            $qb->andWhere($orXSchule);
        }


        //Schuljahr als Filter
        if (isset($parameters['schuljahr']) && $parameters['schuljahr'] !== "") {
            $jahr = $this->em->getRepository(Active::class)->find($parameters['schuljahr']);
            $qb->andWhere('b.active = :jahr')
                ->setParameter('jahr', $jahr);
        }
        //Wochentag als Filter
        if (isset($parameters['wochentag']) && $parameters['wochentag'] !== "") {
            $qb->andWhere('b.wochentag = :wochentag')
                ->setParameter('wochentag', $parameters['wochentag']);
        }
        //block ausgewählt

        if (isset($parameters['block']) && $parameters['block'] !== "") {   // wenn der Block angezeigt werden soll, dann auch von gelöschten Blöcken
            $qb->andWhere('b.id = :block')
                ->setParameter('block', $parameters['block']);

        }else{// sonst immer nur die Kinder anzeigen die an activen Blöcken hängen
            $qb->andWhere('b.deleted = false');
        }
        //Jahrgangsstufe uasgewält
        if (isset($parameters['klasse']) && $parameters['klasse'] !== "") {
            $qb->andWhere('k.klasse = :klasse')
                ->setParameter('klasse', $parameters['klasse']);
        }
        $qb->andWhere('k.fin = 1');
        if ($isApp) {
            $orX = $qb->expr()->orX();

            if (sizeof($user->getSchulen()) == 0) {
                $orX->add('k.schule = -1');
            } else {
                foreach ($user->getSchulen() as $data) {
                    $orX->add('k.schule =:schule' . $data->getId());
                    $qb->setParameter('schule' . $data->getId(), $data);
                }
            }
            $qb->andWhere($orX);
        }
        $qb->addOrderBy('k.klasse','ASC')
            ->addOrderBy('k.nachname','DESC');

        $query = $qb->getQuery();
        $kinder = $query->getResult();

        return $kinder;

    }
}
