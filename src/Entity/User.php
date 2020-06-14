<?php
// src/Entity/User.php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;

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
    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $birthday;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $appToken;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $confirmationTokenApp;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $appCommunicationToken;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $appDetectionToken;

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

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBirthday(): ?\DateTimeInterface
    {
        return $this->birthday;
    }

    public function setBirthday(?\DateTimeInterface $birthday): self
    {
        $this->birthday = $birthday;

        return $this;
    }

    public function getStadt(): ?Stadt
    {
        return $this->stadt;
    }

    public function setStadt(?Stadt $stadt): self
    {
        $this->stadt = $stadt;

        return $this;
    }

    public function getOrganisation(): ?Organisation
    {
        return $this->organisation;
    }

    public function setOrganisation(?Organisation $organisation): self
    {
        $this->organisation = $organisation;

        return $this;
    }

    public function getAppToken(): ?string
    {
        return $this->appToken;
    }

    public function setAppToken(?string $appToken): self
    {
        $this->appToken = $appToken;

        return $this;
    }

    public function getConfirmationTokenApp(): ?string
    {
        return $this->confirmationTokenApp;
    }

    public function setConfirmationTokenApp(?string $confirmationTokenApp): self
    {
        $this->confirmationTokenApp = $confirmationTokenApp;

        return $this;
    }

    public function getAppCommunicationToken(): ?string
    {
        return $this->appCommunicationToken;
    }

    public function setAppCommunicationToken(?string $appCommunicationToken): self
    {
        $this->appCommunicationToken = $appCommunicationToken;

        return $this;
    }

    public function getAppDetectionToken(): ?string
    {
        return $this->appDetectionToken;
    }

    public function setAppDetectionToken(?string $appDetectionToken): self
    {
        $this->appDetectionToken = $appDetectionToken;

        return $this;
    }
}
