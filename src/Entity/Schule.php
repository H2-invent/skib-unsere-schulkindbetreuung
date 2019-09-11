<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SchuleRepository")
 */
class Schule
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Organisation", inversedBy="schule")
     * @ORM\JoinColumn(nullable=false)
     */
    private $organisation;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Zeitblock", mappedBy="schule")
     */
    private $zeitblocks;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\stadt", inversedBy="schules")
     */
    private $stadt;

    public function __construct()
    {
        $this->zeitblocks = new ArrayCollection();
    }

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

    public function getOrganisation(): ?Organisation
    {
        return $this->organisation;
    }

    public function setOrganisation(?Organisation $organisation): self
    {
        $this->organisation = $organisation;

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
            $zeitblock->setSchule($this);
        }

        return $this;
    }

    public function removeZeitblock(Zeitblock $zeitblock): self
    {
        if ($this->zeitblocks->contains($zeitblock)) {
            $this->zeitblocks->removeElement($zeitblock);
            // set the owning side to null (unless already changed)
            if ($zeitblock->getSchule() === $this) {
                $zeitblock->setSchule(null);
            }
        }

        return $this;
    }

    public function getStadt(): ?stadt
    {
        return $this->stadt;
    }

    public function setStadt(?stadt $stadt): self
    {
        $this->stadt = $stadt;

        return $this;
    }
}
