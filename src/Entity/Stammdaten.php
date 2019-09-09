<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\StammdatenRepository")
 */
class Stammdaten
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     */
    private $name;

    /**
     * @ORM\Column(type="text")
     */
    private $vorname;

    /**
     * @ORM\Column(type="text")
     */
    private $strasse;

    /**
     * @ORM\Column(type="text",nullable=true)
     */
    private $adresszusatz;

    /**
     * @ORM\Column(type="integer")
     */
    private $einkommen;

    /**
     * @ORM\Column(type="datetime")
     */
    private $created_at;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getVorname(): ?string
    {
        return $this->vorname;
    }

    public function setVorname(string $vorname): self
    {
        $this->vorname = $vorname;

        return $this;
    }

    public function getStrasse(): ?string
    {
        return $this->strasse;
    }

    public function setStrasse(string $strasse): self
    {
        $this->strasse = $strasse;

        return $this;
    }

    public function getAdresszusatz(): ?string
    {
        return $this->adresszusatz;
    }

    public function setAdresszusatz(string $adresszusatz): self
    {
        $this->adresszusatz = $adresszusatz;

        return $this;
    }

    public function getEinkommen(): ?int
    {
        return $this->einkommen;
    }

    public function setEinkommen(int $einkommen): self
    {
        $this->einkommen = $einkommen;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }
}
