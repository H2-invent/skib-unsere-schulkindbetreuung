<?php

namespace App\Entity;

use App\Repository\PersonenberechtigterRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=PersonenberechtigterRepository::class)
 */
class Personenberechtigter
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     */
    private $vorname;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     */
    private $nachname;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     */
    private $strasse;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $adresszusatz;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     * @Assert\Regex(pattern="/\b(?!01000|99999)(0[1-9]\d{3}|[1-9]\d{4})\b/i")
     */
    private $plz;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     */
    private $stadt;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $phone;

    /**
     * @Assert\Email()
     * @Assert\NotBlank()
     * @ORM\Column(type="text", nullable=true)
     */
    private $email;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $notfallkontakt;

    /**
     * @ORM\ManyToOne(targetEntity=Stammdaten::class, inversedBy="personenberechtigters")
     * @ORM\JoinColumn(nullable=false)
     */
    private $stammdaten;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getStrasse(): ?string
    {
        return $this->strasse;
    }

    public function setStrasse(?string $strasse): self
    {
        $this->strasse = $strasse;

        return $this;
    }

    public function getAdresszusatz(): ?string
    {
        return $this->adresszusatz;
    }

    public function setAdresszusatz(?string $adresszusatz): self
    {
        $this->adresszusatz = $adresszusatz;

        return $this;
    }

    public function getPlz(): ?string
    {
        return $this->plz;
    }

    public function setPlz(?string $plz): self
    {
        $this->plz = $plz;

        return $this;
    }

    public function getStadt(): ?string
    {
        return $this->stadt;
    }

    public function setStadt(?string $stadt): self
    {
        $this->stadt = $stadt;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getNotfallkontakt(): ?string
    {
        return $this->notfallkontakt;
    }

    public function setNotfallkontakt(?string $notfallkontakt): self
    {
        $this->notfallkontakt = $notfallkontakt;

        return $this;
    }

    public function getStammdaten(): ?Stammdaten
    {
        return $this->stammdaten;
    }

    public function setStammdaten(?Stammdaten $stammdaten): self
    {
        $this->stammdaten = $stammdaten;

        return $this;
    }
}
