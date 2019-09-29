<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\KindRepository")
 */
class Kind
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Stammdaten", inversedBy="kinds")
     * @ORM\JoinColumn(nullable=false)
     */
    private $eltern;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $allergie;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $medikamente;

    /**
     * @ORM\Column(type="text")
     */
    private $vorname;

    /**
     * @ORM\Column(type="text")
     */
    private $nachname;

    /**
     * @ORM\Column(type="integer")
     */
    private $klasse;

    /**
     * @ORM\Column(type="integer")
     */
    private $art;

    /**
     * @ORM\Column(type="datetime")
     */
    private $geburtstag;



    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Abwesend", mappedBy="kind")
     */
    private $abwesends;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Zeitblock", mappedBy="kind")
     */
    private $zeitblocks;

    public function __construct()
    {
        $this->zeitblocks = new ArrayCollection();
        $this->abwesends = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEltern(): ?stammdaten
    {
        return $this->eltern;
    }

    public function setEltern(?stammdaten $eltern): self
    {
        $this->eltern = $eltern;

        return $this;
    }

    public function getAllergie(): ?string
    {
        return $this->allergie;
    }

    public function setAllergie(?string $allergie): self
    {
        $this->allergie = $allergie;

        return $this;
    }

    public function getMedikamente(): ?string
    {
        return $this->medikamente;
    }

    public function setMedikamente(?string $medikamente): self
    {
        $this->medikamente = $medikamente;

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

    public function getNachname(): ?string
    {
        return $this->nachname;
    }

    public function setNachname(string $nachname): self
    {
        $this->nachname = $nachname;

        return $this;
    }

    public function getKlasse(): ?int
    {
        return $this->klasse;
    }

    public function setKlasse(int $klasse): self
    {
        $this->klasse = $klasse;

        return $this;
    }

    public function getArt(): ?int
    {
        return $this->art;
    }

    public function setArt(int $art): self
    {
        $this->art = $art;

        return $this;
    }

    public function getGeburtstag(): ?\DateTimeInterface
    {
        return $this->geburtstag;
    }

    public function setGeburtstag(\DateTimeInterface $geburtstag): self
    {
        $this->geburtstag = $geburtstag;

        return $this;
    }

    /**
     * @return Collection|Zeitblock[]
     */
    public function getZeitblocks(): Collection
    {
        return $this->zeitblocks;
    }

    public function addZeitblock(Zeitblock $zeitblock): self
    {
        if (!$this->zeitblocks->contains($zeitblock)) {
            $this->zeitblocks[] = $zeitblock;
            $zeitblock->addKind($this);
        }

        return $this;
    }

    public function removeZeitblock(Zeitblock $zeitblock): self
    {
        if ($this->zeitblocks->contains($zeitblock)) {
            $this->zeitblocks->removeElement($zeitblock);
            $zeitblock->removeKind($this);
        }

        return $this;
    }

    /**
     * @return Collection|Abwesend[]
     */
    public function getAbwesends(): Collection
    {
        return $this->abwesends;
    }

    public function addAbwesend(Abwesend $abwesend): self
    {
        if (!$this->abwesends->contains($abwesend)) {
            $this->abwesends[] = $abwesend;
            $abwesend->setKind($this);
        }

        return $this;
    }

    public function removeAbwesend(Abwesend $abwesend): self
    {
        if ($this->abwesends->contains($abwesend)) {
            $this->abwesends->removeElement($abwesend);
            // set the owning side to null (unless already changed)
            if ($abwesend->getKind() === $this) {
                $abwesend->setKind(null);
            }
        }

        return $this;
    }
}
