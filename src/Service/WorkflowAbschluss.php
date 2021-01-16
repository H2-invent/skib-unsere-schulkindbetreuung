<?php

namespace App\Service;

use App\Entity\Kind;
use App\Entity\Stadt;
use App\Entity\Stammdaten;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;


// <- Add this

class WorkflowAbschluss
{


    private $em;

    public function __construct(Security $security, EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
        $this->user = $security;
    }

    public
    function abschluss(Stammdaten $adresseAktuell, $kind, Stadt $stadt)
    {

        if (!$adresseAktuell->getTracing()) {
            $adresseAktuell->setTracing(md5(uniqid('stammdaten', true)));
        }
        $kundennummern = array();
        $adresseAktuell->setCreatedAt(new \DateTime());
        if ($adresseAktuell->getHistory() > 0) {// es gibt bereits eine alte Historie, diese bsitzt schon ein Fin
            $adresseOld = $this->em->getRepository(Stammdaten::class)->findOneBy(array('tracing' => $adresseAktuell->getTracing(), 'fin' => true));
            $adresseOld->setFin(false);
            $adresseOld->setEndedAt((clone $adresseAktuell->getCreatedAt())->modify('last day of this month'));
            $this->em->persist($adresseOld);
            $kundennummern = $adresseOld->getKundennummerns();
            $kindOld = $adresseOld->getKinds();
            foreach ($kindOld as $data) {
                $data->setFin(false);
                $this->em->persist($data);
            }
        }
        $this->em->flush();
        if ($stadt->getSecCodeAlwaysNew()) {
            $adresseAktuell->setSecCode(substr(str_shuffle(MD5(microtime())), 0, 6));
        } else {
            if ($adresseAktuell->getHistory() === 0) {
                $adresseAktuell->setSecCode(substr(str_shuffle(MD5(microtime())), 0, 6));
            } else {
                $adresseAktuell->setSecCode($adresseOld->getSecCode());
            }
        }


        foreach ($kundennummern as $data) {
            $kn = clone $data;
            $kn->setStammdaten($adresseAktuell);
            $this->em->persist($kn);
        }

        $adressNew = clone $adresseAktuell;
        $adressNew->setSaved(false);
        $adressNew->setHistory($adressNew->getHistory() + 1);
        $adressNew->setSecCode(null);
        $adresseAktuell->setFin(true);
        $adresseAktuell->setSaved(true);
        $this->em->persist($adressNew);
        $kind = $adresseAktuell->getKinds();
        foreach ($kind as $data) {
            if (!$data->getTracing()) {
                $data->setTracing(md5(uniqid('kind', true)));
            }
            $kindNew = clone $data;
            $kindNew->setHistory($kindNew->getHistory() + 1);
            $data->setSaved(true);
            $data->setFin(true);
            $this->em->persist($data);
            $kindNew->setEltern($adressNew);

            foreach ($data->getZeitblocks() as $zb) {
                $zb->addKind($kindNew);
            }

            foreach ($data->getBeworben() as $zb) {
                $kindNew->addBeworben($zb);
            }
            $this->em->persist($kindNew);
        }
        $this->em->persist($adresseAktuell);
        $this->em->persist($adressNew);
        $this->em->flush();
    }
}