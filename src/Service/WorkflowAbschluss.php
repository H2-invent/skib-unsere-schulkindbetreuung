<?php

namespace App\Service;

use App\Entity\Kind;
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
    function abschluss(Stammdaten $adresse, $kind)
    {
        $adresse->setSecCode(substr(str_shuffle(MD5(microtime())), 0, 6));

        if (!$adresse->getTracing()) {
            $adresse->setTracing(md5(uniqid('stammdaten', true)));
        }
        $kundennummern = array();
        $adresse->setCreatedAt(new \DateTime());
        if ($adresse->getHistory() > 0) {// es gibt bereits eine alte Historie, diese bsitzt schon ein Fin
            $adresseOld = $this->em->getRepository(Stammdaten::class)->findOneBy(array('tracing' => $adresse->getTracing(), 'fin' => true));
            $adresseOld->setFin(false);
            $adresseOld->setEndedAt((clone $adresse->getCreatedAt())->modify('last day of this month'));
            $this->em->persist($adresseOld);
            $kundennummern = $adresseOld->getKundennummerns();
        }


        foreach ($kundennummern as $data) {
            $kn = clone $data;
            $kn->setStammdaten($adresse);
            $this->em->persist($kn);
        }

        $adressCopy = clone $adresse;

        $adressCopy->setSaved(false);
        $adressCopy->setHistory($adressCopy->getHistory() + 1);
        $adressCopy->setSecCode(null);
        $adresse->setFin(true);
        $adresse->setSaved(true);
        $this->em->persist($adressCopy);
        foreach ($kind as $data) {
            if (!$data->getTracing()) {
                $data->setTracing(md5(uniqid('kind', true)));
            }
            if ($data->getHistory() > 0) {
                $kindOld = $this->em->getRepository(Kind::class)->findOneBy(array('fin' => true, 'tracing' => $data->getTracing()));
                $kindOld->setFin(false);
                $this->em->persist($kindOld);
            }
            $kindNew = clone $data;
            $kindNew->setHistory($kindNew->getHistory() + 1);
            $data->setSaved(true);
            $data->setFin(true);
            $this->em->persist($data);
            $kindNew->setEltern($adressCopy);

            foreach ($data->getZeitblocks() as $zb) {
                $zb->addKind($kindNew);
            }
            $this->em->persist($kindNew);
            foreach ($data->getBeworben() as $zb) {
                $kindNew->addBeworben($zb);
            }

        }
        $this->em->persist($adresse);
        $this->em->persist($adressCopy);
        $this->em->flush();
    }
}
