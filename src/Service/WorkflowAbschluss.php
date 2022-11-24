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
    function abschluss(Stammdaten $adresseAktuell, Stadt $stadt, ?Kind $kindToEdit = null):Stammdaten
    {

        if (!$adresseAktuell->getTracing()) {//Die Stammdaten sind neu und es gibt noch keine Tracing ID
            $adresseAktuell->setTracing(md5(uniqid('stammdaten', true)));
        }

        $kundennummern = array();
        $adresseAktuell->setCreatedAt(new \DateTime());//setzte die aktuelle Zeit als created At
        // es gibt bereits eine alte Historie, diese besitzt schon ein Fin
        $adresseOld = $this->em->getRepository(Stammdaten::class)->findOneBy(array('tracing' => $adresseAktuell->getTracing()), array('created_at' => 'ASC'));
        if ( $adresseOld) {
            $kundennummern = $adresseOld->getKundennummerns();
        }


        if ($stadt->getSecCodeAlwaysNew()) {//soll der Sec-Code jedesmal neu gesetzt werde , dann wird hier ein neues Code generiert
            $adresseAktuell->setSecCode(substr(str_shuffle(MD5(microtime())), 0, 6));
        } else {
            if (!$adresseOld) {
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
        $adressNew->setSecCode(null);//die neue Adresse hat noch keinen neuen SecCode
        $adressNew->setCreatedAt(null);//we set the createdAt to null so we know that whit is the working copy
        $this->em->persist($adressNew);
        $kinderAktuell = $adresseAktuell->getKinds();//alle Kinder werden von der aktuellen Arbeitskopie ausgelesen
        if ($kindToEdit){
            foreach ($kinderAktuell as $data){
                if ($data !== $kindToEdit){
                    $data->setStartDate(null);
                }
            }
        }
        foreach ($kinderAktuell as $data) {
            if (!$data->getTracing()) {
                $data->setTracing(md5(uniqid('kind', true)));//wir setzten eine tracing ID, falls diese noch nciht vorhanfden ist.
            }
            $kindNew = clone $data;//hier wird nun die Kinder arbitskopie erstellt
            $this->em->persist($data);
            $kindNew->setEltern($adressNew);
            $kindNew->setStartDate(null);
            foreach ($data->getZeitblocks() as $zb) {//alle bisherigen GEBUCHTEN Zietböocke werden zu der Arbeitskopie hinzugefügt
                $zb->addKind($kindNew);
            }

            foreach ($data->getBeworben() as $zb) {//alle BEWORBENEN Zeitblöcke werden zu der Arbeitskopie hinzugefügt
                $kindNew->addBeworben($zb);
            }

            $this->em->persist($kindNew);

            foreach ($adresseAktuell->getPersonenberechtigters() as $dataP) { // alle personen berechtigten werden kopiert und an die Arbeitskopie der Stammdaten angehängt
                $persNeu = clone $dataP;
                $persNeu->setStammdaten($adressNew);
                $this->em->persist($persNeu);
            }
            foreach ($adresseAktuell->getGeschwisters() as $dataG) {//alle zusätlichen GGeschwister werden kopiert und an die neue Arbeitskopie angehängt
                $geschNeu = clone $dataG;
                $geschNeu->setStammdaten($adressNew);
                $this->em->persist($geschNeu);
            }
        }

        $this->em->persist($adresseAktuell);
        $this->em->persist($adressNew);
        $this->em->flush();
        return $adresseAktuell;
    }
}