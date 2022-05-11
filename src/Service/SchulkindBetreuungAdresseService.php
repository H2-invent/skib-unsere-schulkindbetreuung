<?php
/**
 * Created by PhpStorm.
 * User: Emanuel
 * Date: 03.10.2019
 * Time: 19:01
 */

namespace App\Service;



use App\Entity\Stammdaten;

use Doctrine\ORM\EntityManagerInterface;


class SchulkindBetreuungAdresseService
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;

    }

    public function setAdress(Stammdaten $adresse, bool $hasRole,$ipAdress)
    {

        if ($hasRole) {
            $adresse->setEmailConfirmed(true);
            $adresse->setConfirmEmailSend(true);
            $adresse->setConfirmationCode(str_shuffle(MD5(microtime())), 0, 6);
            $adresse->setIpAdresse($ipAdress);
            $adresse->setConfirmDate(new \DateTime());
        }
        $adresse->setEmailDoubleInput($adresse->getEmail());
        $adresse->setFin(false);
        $this->em->persist($adresse);
        $this->em->flush();
        return $adresse;
    }

    public function setUID(Stammdaten $adresse)
    {
        if ($adresse->getUid() === null) {
            $adresse->setUid(md5(uniqid('', true)))
                ->setAngemeldet(false);
            $adresse->setCreatedAt(new \DateTime());
        }
        return $adresse;
    }
}
