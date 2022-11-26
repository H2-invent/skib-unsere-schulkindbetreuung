<?php

namespace App\Service;

use App\Entity\Active;
use App\Entity\Anwesenheit;
use App\Entity\Kind;
use App\Entity\Organisation;
use App\Entity\Schule;
use App\Entity\Stadt;
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

    /**
     * @return Kind[]
     */
    public function searchChild($parameters, ?Organisation $organisation, $isApp, User $user, ?\DateTime $dateFrom = null, ?\DateTime $dateTo = null, Stadt $stadt = null)
    {
        if (!$dateFrom) {
            $dateFrom = new \DateTime();
        }
        $diff = false;
        $qb = $this->em->getRepository(Kind::class)->createQueryBuilder('k')
            ->innerJoin('k.eltern', 'eltern')
            ->andWhere('eltern.created_at IS NOT NULL');

        if (!$dateTo){
            $qb->andWhere('k.startDate <= :fromDate')->setParameter('fromDate', $dateFrom);
        }else{
            $qb->andWhere('k.startDate >= :fromDate')->setParameter('fromDate', $dateFrom)
            ->andWhere('k.startDate <= :endDate')->setParameter('endDate', $dateTo);
            $diff = true;
        }


        //Schule als Filter ausgewählt
        if (isset($parameters['schule']) && $parameters['schule'] !== "") {

            $schule = $this->em->getRepository(Schule::class)->find($parameters['schule']);
            $qb->innerJoin('k.schule','schule')
                ->andWhere('schule = :schule')->setParameter('schule', $schule);
        } elseif ($stadt) {
            $qb->innerJoin('k.schule', 'schule')
                ->innerJoin('schule.stadt', 'stadt')
                ->andWhere('stadt = :stadt')->setParameter('stadt', $stadt);
        } else {
            $qb->innerJoin('k.schule', 'schule');
            $orXSchule = $qb->expr()->orX();
            $schulen = sizeof($user->getSchulen()) === 0 ? $organisation->getSchule() : $user->getSchulen();
            foreach ($schulen as $data) {
                $orXSchule->add('schule = :schule' . $data->getId());
                $qb->setParameter('schule' . $data->getId(), $data);
            }
            $qb->andWhere($orXSchule);
        }

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




        $qb->addOrderBy('k.startDate', 'DESC')
            ->addOrderBy('eltern.created_at', 'ASC')
            ->addOrderBy('k.klasse', 'ASC')
            ->addOrderBy('k.nachname', 'DESC');

        $query = $qb->getQuery();
        $kinder = $query->getResult();

        $kinderRes = array();
        foreach ($kinder as $data) {
            $kindTmp = isset($kinderRes[$data->getTracing()]) ? $kinderRes[$data->getTracing()] : null;
            if (!$kindTmp) {
                $kinderRes[$data->getTracing()] = $data;
            } else {
                if ($kindTmp->getStartDate() < $data->getStartDate()) {
                    $kinderRes[$data->getTracing()] == $data;
                } elseif ($kindTmp->getStartDate() == $data->getStartDate()) {
                    if ($kindTmp->getEltern()->getCreatedAt() < $data->getEltern()->getCreatedAt()) {
                        $kinderRes[$data->getTracing()] == $data;
                    }
                }
            }
        }

        foreach ($kinderRes as $key=>$data){
            $check = $this->checkKindOfParameter($parameters,$data,$diff);
            if (!$check){
                unset($kinderRes[$key]);
            }
        }

        return $kinderRes;

    }
    public function checkKindOfParameter($parameters, Kind $kind, $diff = false){
        $result = true;
        if (sizeof($kind->getRealZeitblocks()) === 0 && !$diff){
            return false;
        }
        //        //Schuljahr als Filter
        if (isset($parameters['schuljahr']) && $parameters['schuljahr'] !== ""&& !$diff) {
            $jahr = $this->em->getRepository(Active::class)->find($parameters['schuljahr']);
          if ($kind->getRealZeitblocks()[0]->getActive() !== $jahr){
              return false;
          }
        }
//        //Wochentag als Filter
        if (isset($parameters['wochentag']) && $parameters['wochentag'] !== "") {
            foreach ($kind->getRealZeitblocks() as $data){
                if ($data->getWochentag() == $parameters['wochentag']){
                    $result = true;
                    break;
                }
                $result = false;
            }
        }
        if ($result === false){

            return false;
        }
//        //block ausgewählt
//
        if (isset($parameters['block']) && $parameters['block'] !== "") {   // wenn der Block angezeigt werden soll, dann auch von gelöschten Blöcken
            foreach ($kind->getRealZeitblocks() as $data){
                if ($data->getId() == $parameters['block']){
                    $result = true;
                    break;
                }
                $result = false;
            }
        } else {// sonst immer nur die Kinder anzeigen die an activen Blöcken hängen
            foreach ($kind->getRealZeitblocks() as $data){
                if ($data->getDeleted() === false ){
                    $result = true;
                    break;
                }
                $result  = false;
            }
        }
        if ($result === false){
            return false;
        }

        //Jahrgangsstufe uasgewält
        if (isset($parameters['klasse']) && $parameters['klasse'] !== "") {
            if ($kind->getKlasse() != $parameters['klasse']){

                return false;
            }
        }
        return $result;
    }
}
