<?php
// src/Entity/User.php

namespace App\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;

/**
* @ORM\Entity
* @ORM\Table(name="fos_user")
*/
class User extends BaseUser
{
/**
* @ORM\Id
* @ORM\Column(type="integer")
* @ORM\GeneratedValue(strategy="AUTO")
*/
protected $id;
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Stadt")
     * @ORM\JoinColumn(nullable=true)
     */
    private $stadt;
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Organisation")
     * @ORM\JoinColumn(nullable=true)
     */
    private $organisation;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $vorname;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $nachname;

public function __construct()
{
parent::__construct();
// your own logic
}

public function getVorname(): ?string
{
    return $this->vorname;
}

public function setVorname(?string $vorname): self
{
    $this->vorname = $vorname;

    return $this;
}

public function getNachname(): ?string
{
    return $this->nachname;
}

public function setNachname(?string $nachname): self
{
    $this->nachname = $nachname;

    return $this;
}
}