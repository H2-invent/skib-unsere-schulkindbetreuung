<?php
// src/Entity/User.php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Entity
 * @ORM\Table(name="fos_user")
 */
class User extends UserBase
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

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $AppImei;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $appOS;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $appDevice;

    /**
     * @ORM\Column(type="boolean")
     */
    private $appSettingsSaved = false;

    /**
     * @ORM\ManyToMany(targetEntity=Schule::class, inversedBy="users")
     */
    private $schulen;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $auth0Id;

    /**
     * @ORM\Column(type="text")
     */
    private $email;


    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastLogin;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $keycloakId;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    protected $roles = [];

    /**
     * @ORM\Column(type="boolean")
     */
    private $enabled = false;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $invitationToken;


    public function __construct()
    {
        parent::__construct();
        $this->schulen = new ArrayCollection();
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

    public function getAppImei(): ?string
    {
        return $this->AppImei;
    }

    public function setAppImei(?string $AppImei): self
    {
        $this->AppImei = $AppImei;

        return $this;
    }

    public function getAppOS(): ?string
    {
        return $this->appOS;
    }

    public function setAppOS(?string $appOS): self
    {
        $this->appOS = $appOS;

        return $this;
    }

    public function getAppDevice(): ?string
    {
        return $this->appDevice;
    }

    public function setAppDevice(?string $appDevice): self
    {
        $this->appDevice = $appDevice;

        return $this;
    }

    public function getAppSettingsSaved(): ?bool
    {
        return $this->appSettingsSaved;
    }

    public function setAppSettingsSaved(bool $appSettingsSaved): self
    {
        $this->appSettingsSaved = $appSettingsSaved;

        return $this;
    }

    /**
     * @return Collection|Schule[]
     */
    public function getSchulen(): Collection
    {
        return $this->schulen;
    }

    public function addSchulen(Schule $schulen): self
    {
        if (!$this->schulen->contains($schulen)) {
            $this->schulen[] = $schulen;
        }

        return $this;
    }

    public function removeSchulen(Schule $schulen): self
    {
        if ($this->schulen->contains($schulen)) {
            $this->schulen->removeElement($schulen);
        }

        return $this;
    }

    public function getAuth0Id(): ?string
    {
        return $this->auth0Id;
    }

    public function setAuth0Id(?string $auth0Id): self
    {
        $this->auth0Id = $auth0Id;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }


    public function getLastLogin(): ?\DateTimeInterface
    {
        return $this->lastLogin;
    }

    public function setLastLogin(?\DateTimeInterface $lastLogin): self
    {
        $this->lastLogin = $lastLogin;

        return $this;
    }

    public function getKeycloakId(): ?string
    {
        return $this->keycloakId;
    }

    public function setKeycloakId(?string $keycloakId): self
    {
        $this->keycloakId = $keycloakId;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }
    public function getUserIdentifier():string
    {
        return $this->email;
    }

    public function getEnabled(): ?bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }
    public function isEnabled(): bool
    {
       return $this->enabled;


    }

    public function getInvitationToken(): ?string
    {
        return $this->invitationToken;
    }

    public function setInvitationToken(?string $invitationToken): self
    {
        $this->invitationToken = $invitationToken;

        return $this;
    }


}
