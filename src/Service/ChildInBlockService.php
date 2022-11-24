<?php

namespace App\Service;

use App\Entity\Active;
use App\Entity\Kind;
use App\Entity\Schule;
use App\Entity\Zeitblock;
use Doctrine\ORM\EntityManagerInterface;

class ChildInBlockService
{
    private $em;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    /**
     * @return Kind[] Returns an array of Kind objects
     */
    public function getCurrentChildOfZeitblock(Zeitblock $zeitblock, \DateTime $dateToCheck)
    {
        $kinder = $zeitblock->getKind();
        $checkTracing = array();
        $res = array();
        foreach ($kinder as $data) {
            $tracing = $data->getTracing();
            if (!in_array($tracing, $checkTracing)) {
                $currentChild = $this->em->getRepository(Kind::class)->findHistoryOfThisChild($data);
                $currentChild = $this->sortChilds($currentChild);
                $child = $this->checkIfChildIsNow($currentChild, $zeitblock, $dateToCheck);
                if ($child) {
                    $res[] = $child;
                }
                $checkTracing[] = $tracing;
            }
        }
        return $res;
    }

    /**
     * @return Kind[] Returns an array of Kind objects
     */
    public function getCurrentChildAndFuturerChildOfZeitblock(Zeitblock $zeitblock, \DateTime $dateToCheck)
    {
        $kinder = $zeitblock->getKind();
        $checkTracing = array();
        $res = array();
        foreach ($kinder as $data) {
            $tracing = $data->getTracing();
            if (!in_array($tracing, $checkTracing)) {
                $currentChild = $this->em->getRepository(Kind::class)->findHistoryOfThisChild($data);
                $currentChild = $this->sortChilds($currentChild);
                $child = $this->checkIfChildIsNowOrInFuture($currentChild, $zeitblock, $dateToCheck);
                if ($child) {
                    $res[] = $child;
                }
                $checkTracing[] = $tracing;
            }
        }
        return $res;
    }

    /**
     * @param Kind[] $kinds
     */
    private function sortChilds(array $kinds)
    {
        usort($kinds, function (Kind $a, Kind $b): int {
            if ($a->getStartDate() == $b->getStartDate()) {
                return $a->getEltern()->getCreatedAt() <=> $a->getEltern()->getCreatedAt();
            }
            return $a->getStartDate() <=> $b->getStartDate();
        });
        return $kinds;
    }

    /**
     * @param Kind[] $kinds
     * @param Zeitblock $zeitblock
     * @param \DateTime $date
     */
    public function checkIfChildIsNowOrInFuture($kinds, Zeitblock $zeitblock, \DateTime $date): ?Kind
    {
        $lastChecked = null;
        foreach (array_reverse($kinds) as $data) {
            // ich befinde mich vor dem start-datum
            if ($data->getStartDate() > $date) {
                if (in_array($zeitblock, $data->getZeitblocks()->toArray())) {
                    if (!$lastChecked || $lastChecked->getStartDate() != $data->getStartDate()) {
                        return $data;
                    }
                }

            } else {
                // ich befinde mich nach dem start-datum
                if (in_array($zeitblock, $data->getZeitblocks()->toArray())) {
                    return $data;
                } else {
                    return null;
                }
            }
            $lastChecked = $data;
        }
        return null;
    }

    /**
     * @param Kind[] $kinds
     * @param Zeitblock $zeitblock
     * @param \DateTime $date
     */
    public function checkIfChildIsNow($kinds, Zeitblock $zeitblock, \DateTime $date): ?Kind
    {
        $lastChecked = null;
        foreach (array_reverse($kinds) as $data) {

            if ($data->getStartDate() < $date) { // ich befinde mich vor dem start-datum

                if (in_array($zeitblock, $data->getZeitblocks()->toArray())) {
                    return $data;
                } else {
                    return null;
                }
            }

        }
        return null;
    }

    public function getChildFOrShoolinFuture(Schule $schule, Active $active, \DateTime $startDate)
    {
        $zb = $this->em->getRepository(Zeitblock::class)->findBy(array('deleted' => false, 'active' => $active, 'schule' => $schule));
        $child = array();

        foreach ($zb as $data) {
            $ch = $this->getCurrentChildAndFuturerChildOfZeitblock($data, $startDate);
            $child = array_merge($ch,$child);
        }
        $child = array_unique($child);
        return $child;
    }
}