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

        if (!$adresseAktuell->getTracing()) {//Die Stammdaten sind neu und es gibt noch keine Tracing ID
            $adresseAktuell->setTracing(md5(uniqid('stammdaten', true)));
        }

        $kundennummern = array();
        $adresseAktuell->setCreatedAt(new \DateTime());//setzte die aktuelle Zeit als created At
        if ($adresseAktuell->getHistory() > 0) {// es gibt bereits eine alte Historie, diese bsitzt schon ein Fin
            $adresseOld = $this->em->getRepository(Stammdaten::class)->findOneBy(array('tracing' => $adresseAktuell->getTracing(), 'fin' => true));
            $adresseOld->setFin(false);
            $adresseOld->setEndedAt((clone $adresseAktuell->getCreatedAt())->modify('last day of this month'));
            $this->em->persist($adresseOld);
            $kundennummern = $adresseOld->getKundennummerns();
            $kindOld = $adresseOld->getKinds();
            foreach ($kindOld as $data) {//Alle kinder werden nun auch als ungeültig markiert. Dies gehschiht durch das sethen von fin false
                $data->setFin(false);
                $this->em->persist($data);
            }
        }
        $this->em->flush();
        if ($stadt->getSecCodeAlwaysNew()) {//soll der Sec-Code jedesmal neu gesetzt werde , dann wird hier ein neues Code generiert
            $adresseAktuell->setSecCode(substr(str_shuffle(MD5(microtime())), 0, 6));
        } else {
            if ($adresseAktuell->getHistory() === 0) {
                $adresseAktuell->setSecCode(substr(str_shuffle(MD5(microtime())), 0, 6));
            } else {
                $adresseAktuell->setSecCode($adresseOld->getSecCode());
            }
        }


        foreach ($kundennummern as $data) {//es werden alle Kundennummer nun geklont und an die aktuelle Adrese gehängt.
            $kn = clone $data;
            $kn->setStammdaten($adresseAktuell);
            $this->em->persist($kn);
        }

        $adressNew = clone $adresseAktuell;//hier erstellen wir nun unsere neue Arbeitsvorlage. Alle späteren änderungen geschen auf dieser vorlage.
        $adressNew->setSaved(false);//die Arbeitskopie erhält das flag saved false, da wir dise noh niht gespeichert ist, sondern lediglich als arbeitskopie verwendet wird.
        $adressNew->setHistory($adressNew->getHistory() + 1);
        $adressNew->setSecCode(null);//die neue Adresse hat noch keinen neuen SecCode
        $adresseAktuell->setFin(true);//die neue Adresse erhält nun den Flag das diese nun den aktuellen Datensatz hält
        $adresseAktuell->setSaved(true);//Der bisherige Arbeitsstand wird gesichert
        $this->em->persist($adressNew);
        $kind = $adresseAktuell->getKinds();//alle Kinder werden von der aktuellen Arbeitskopie ausgelesen
        foreach ($kind as $data) {
            if (!$data->getTracing()) {
                $data->setTracing(md5(uniqid('kind', true)));//wir setzten eine tracing ID, falls diese noch nciht vorhanfden ist.
            }
            $kindNew = clone $data;//hier wird nun die Kinder arbitskopie erstellt
            $kindNew->setHistory($kindNew->getHistory() + 1);//hochdrehen der Kinder historie
            $data->setSaved(true);//die aktuelle Arbeitskopie wird gespeichert
            $data->setFin(true);
            $this->em->persist($data);
            $kindNew->setEltern($adressNew);

            foreach ($data->getZeitblocks() as $zb) {//alle bisherigen GEBUCHTEN Zietböocke werden zu der Arbeitskopie hinzugefügt
                $zb->addKind($kindNew);
            }

            foreach ($data->getBeworben() as $zb) {//alle BEWORBENEN Zeitblöcke werden zu der Arbeitskopie hinzugefügt
                $kindNew->addBeworben($zb);
            }

            $this->em->persist($kindNew);
            foreach ($adresseAktuell->getPersonenberechtigters() as $dataP){ // alle personen berechtigten werden kopiert und an die Arbeitskopie der Stammdaten angehängt
                $persNeu = clone $dataP;
                $persNeu->setStammdaten($adressNew);
                $this->em->persist($persNeu);
            }
            foreach ($adresseAktuell->getGeschwisters() as $dataG){//alle zusätlichen GEschwister werden kopiert und an die neue Arbeitskopie angehängt
                $geschNeu = clone $dataG;
                $geschNeu->setStammdaten($adressNew);
                $this->em->persist($geschNeu);
            }
        }
        $this->em->persist($adresseAktuell);
        $this->em->persist($adressNew);
        $this->em->flush();
    }
}