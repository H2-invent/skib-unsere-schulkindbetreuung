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

    public function setAdress(Stammdaten $addresse, bool $hasRole, $ipAdress)
    {

        if ($hasRole) {
            $addresse->setEmailConfirmed(true);
            $addresse->setConfirmEmailSend(true);
            $addresse->setConfirmationCode(str_shuffle(MD5(microtime())), 0, 6);
            $addresse->setIpAdresse($ipAdress);
            $addresse->setConfirmDate(new \DateTime());
        }
        $addresse->setEmailDoubleInput($addresse->getEmail());
        $addresse->setFin(false);
        $this->em->persist($addresse);
        $this->em->flush();
        return $addresse;
    }

    public function setUID(Stammdaten $adresse)
    {
        if ($adresse->getUid() === null) {
            $adresse->setUid(md5(uniqid('', true)));
        }
        return $adresse;
    }
}
