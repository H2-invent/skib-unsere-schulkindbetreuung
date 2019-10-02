<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ZeitblockRepository")
 */
class Zeitblock
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="time")
     */
    private $von;

    /**
     * @ORM\Column(type="time")
     */
    private $bis;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Schule", inversedBy="zeitblocks")
     * @ORM\JoinColumn(nullable=false)
     */
    private $schule;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Kind", inversedBy="zeitblocks")
     */
    private $kind;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Active")
     */
    private $active;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Abwesend", mappedBy="zeitblock")
     */
    private $abwesenheit;

    /**
     * @ORM\Column(type="integer")
     */
    private $wochentag;



    /**
     * @ORM\Column(type="integer")
     */
    private $ganztag;

    /**
     * @ORM\Column(type="json_array")
     */
    private $preise = [];

    public function __construct()
    {
        $this->kind = new ArrayCollection();
        $this->abwesenheit = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getSchule(): ?Schule
    {
        return $this->schule;
    }

    public function setSchule(?Schule $schule): self
    {
        $this->schule = $schule;

        return $this;
    }

    /**
     * @return Collection|Kind[]
     */
    public function getKind(): Collection
    {
        return $this->kind;
    }

    public function addKind(Kind $kind): self
    {
        if (!$this->kind->contains($kind)) {
            $this->kind[] = $kind;
        }

        return $this;
    }

    public function removeKind(Kind $kind): self
    {
        if ($this->kind->contains($kind)) {
            $this->kind->removeElement($kind);
        }

        return $this;
    }

    public function getActive(): ?Active
    {
        return $this->active;
    }

    public function setActive(?Active $active): self
    {
        $this->active = $active;

        return $this;
    }

    /**
     * @return Collection|Abwesend[]
     */
    public function getAbwesenheit(): Collection
    {
        return $this->abwesenheit;
    }

    public function addAbwesenheit(Abwesend $abwesenheit): self
    {
        if (!$this->abwesenheit->contains($abwesenheit)) {
            $this->abwesenheit[] = $abwesenheit;
            $abwesenheit->setZeitblock($this);
        }

        return $this;
    }

    public function removeAbwesenheit(Abwesend $abwesenheit): self
    {
        if ($this->abwesenheit->contains($abwesenheit)) {
            $this->abwesenheit->removeElement($abwesenheit);
            // set the owning side to null (unless already changed)
            if ($abwesenheit->getZeitblock() === $this) {
                $abwesenheit->setZeitblock(null);
            }
        }

        return $this;
    }

    public function getWochentag(): ?int
    {
        return $this->wochentag;
    }

    public function setWochentag(int $wochentag): self
    {
        $this->wochentag = $wochentag;

        return $this;
    }
    public function getWochentagString(){

       switch ($this->wochentag){
           case 0:
               return "Montag";
               break;
           case 1:
               return "Dienstag";
               break;
           case 2:
               return "Mittwoch";
               break;
           case 3:
               return "Donnerstag";
               break;
           case 4:
               return "Freitag";
               break;
           case 5:
               return "Samstag";
               break;
           case 6:
               return "Sonntag";
               break;
           default:
               return "keine Angabe";
               break;

       }
    }


    public function getGanztag(): ?int
    {
        return $this->ganztag;
    }

    public function setGanztag(int $ganztag): self
    {
        $this->ganztag = $ganztag;

        return $this;
    }

    public function getPreise(): ?array
    {
        return $this->preise;
    }

    public function setPreise(array $preise): self
    {
        $this->preise = $preise;

        return $this;
    }
}
