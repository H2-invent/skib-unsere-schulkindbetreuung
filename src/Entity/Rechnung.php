<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RechnungRepository")
 */
class Rechnung
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
    private $pdf;

    /**
     * @ORM\Column(type="float")
     */
    private $summe;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Zeitblock", inversedBy="rechnungen")
     */
    private $zeitblocks;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Stammdaten", inversedBy="rechnungs")
     * @ORM\JoinColumn(nullable=false)
     */
    private $stammdaten;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Sepa", inversedBy="rechnungen",cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
     */
    private $sepa;

    /**
     * @ORM\Column(type="text",nullable=true)
     */
    private $rechnungsnummer;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Kind", inversedBy="rechnungen")
     */
    private $kinder;

    /**
     * @ORM\Column(type="datetime")
     */
    private $von;

    /**
     * @ORM\Column(type="datetime")
     */
    private $bis;

    public function __construct()
    {
        $this->zeitblocks = new ArrayCollection();
        $this->kinder = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPdf()
    {
        return $this->pdf;
    }

    public function setPdf($pdf): self
    {
        $this->pdf = $pdf;

        return $this;
    }

    public function getSumme(): ?float
    {
        return $this->summe;
    }

    public function setSumme(float $summe): self
    {
        $this->summe = $summe;

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

    /**
     * @return Collection|zeitblock[]
     */
    public function getZeitblocks(): Collection
    {
        return $this->zeitblocks;
    }

    public function addZeitblock(zeitblock $zeitblock): self
    {
        if (!$this->zeitblocks->contains($zeitblock)) {
            $this->zeitblocks[] = $zeitblock;
        }

        return $this;
    }

    public function removeZeitblock(zeitblock $zeitblock): self
    {
        if ($this->zeitblocks->contains($zeitblock)) {
            $this->zeitblocks->removeElement($zeitblock);
        }

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

    public function getSepa(): ?Sepa
    {
        return $this->sepa;
    }

    public function setSepa(?Sepa $sepa): self
    {
        $this->sepa = $sepa;

        return $this;
    }

    public function getRechnungsnummer(): ?string
    {
        return $this->rechnungsnummer;
    }

    public function setRechnungsnummer(string $rechnungsnummer): self
    {
        $this->rechnungsnummer = $rechnungsnummer;

        return $this;
    }

    /**
     * @return Collection|Kind[]
     */
    public function getKinder(): Collection
    {
        return $this->kinder;
    }

    public function addKinder(Kind $kinder): self
    {
        if (!$this->kinder->contains($kinder)) {
            $this->kinder[] = $kinder;
        }

        return $this;
    }

    public function removeKinder(Kind $kinder): self
    {
        if ($this->kinder->contains($kinder)) {
            $this->kinder->removeElement($kinder);
        }

        return $this;
    }

    public function getVon(): ?\DateTimeInterface
    {
        return $this->von;
    }

    public function setVon(\DateTimeInterface $von): self
    {
        $this->von = $von;

        return $this;
    }

    public function getBis(): ?\DateTimeInterface
    {
        return $this->bis;
    }

    public function setBis(\DateTimeInterface $bis): self
    {
        $this->bis = $bis;

        return $this;
    }
}
