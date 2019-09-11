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
     * @ORM\ManyToMany(targetEntity="App\Entity\Kind", inversedBy="zeitblocks")
     */
    private $kind;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\schule", inversedBy="zeitblocks")
     */
    private $schule;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Active", mappedBy="zeitblock", cascade={"persist", "remove"})
     */
    private $active;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Abwesend", mappedBy="zeitblock")
     */
    private $abwesends;



    public function __construct()
    {
        $this->kind = new ArrayCollection();
        $this->abwesends = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection|kind[]
     */
    public function getKind(): Collection
    {
        return $this->kind;
    }

    public function addKind(kind $kind): self
    {
        if (!$this->kind->contains($kind)) {
            $this->kind[] = $kind;
        }

        return $this;
    }

    public function removeKind(kind $kind): self
    {
        if ($this->kind->contains($kind)) {
            $this->kind->removeElement($kind);
        }

        return $this;
    }

    public function getSchule(): ?schule
    {
        return $this->schule;
    }

    public function setSchule(?schule $schule): self
    {
        $this->schule = $schule;

        return $this;
    }

    public function getActive(): ?Active
    {
        return $this->active;
    }

    public function setActive(?Active $active): self
    {
        $this->active = $active;

        // set (or unset) the owning side of the relation if necessary
        $newZeitblock = $active === null ? null : $this;
        if ($newZeitblock !== $active->getZeitblock()) {
            $active->setZeitblock($newZeitblock);
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
            $abwesend->setZeitblock($this);
        }

        return $this;
    }

    public function removeAbwesend(Abwesend $abwesend): self
    {
        if ($this->abwesends->contains($abwesend)) {
            $this->abwesends->removeElement($abwesend);
            // set the owning side to null (unless already changed)
            if ($abwesend->getZeitblock() === $this) {
                $abwesend->setZeitblock(null);
            }
        }

        return $this;
    }


}
