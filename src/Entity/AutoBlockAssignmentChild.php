<?php

namespace App\Entity;

use App\Repository\AutoBlockAssignmentChildRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AutoBlockAssignmentChildRepository::class)]
#[ORM\Index(fields: ['weight'])]
class AutoBlockAssignmentChild
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(cascade: ['persist'], inversedBy: 'children')]
    #[ORM\JoinColumn(nullable: false)]
    private ?AutoBlockAssignment $autoBlockAssignment = null;

    #[ORM\OneToMany(mappedBy: 'child', targetEntity: AutoBlockAssignmentChildZeitblock::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $zeitblocks;

    #[ORM\OneToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Kind $kind = null;

    #[ORM\Column]
    private ?float $weight = null;

    public function __construct()
    {
        $this->zeitblocks = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAutoBlockAssignment(): ?AutoBlockAssignment
    {
        return $this->autoBlockAssignment;
    }

    public function setAutoBlockAssignment(?AutoBlockAssignment $autoBlockAssignment): self
    {
        $this->autoBlockAssignment = $autoBlockAssignment;

        return $this;
    }

    /**
     * @return Collection<int, AutoBlockAssignmentChildZeitblock>
     */
    public function getZeitblocks(): Collection
    {
        return $this->zeitblocks;
    }

    public function addZeitblock(AutoBlockAssignmentChildZeitblock $zeitblock): self
    {
        if (!$this->zeitblocks->contains($zeitblock)) {
            $this->zeitblocks->add($zeitblock);
            $zeitblock->setChild($this);
        }

        return $this;
    }

    public function removeZeitblock(AutoBlockAssignmentChildZeitblock $zeitblock): self
    {
        if ($this->zeitblocks->removeElement($zeitblock)) {
            // set the owning side to null (unless already changed)
            if ($zeitblock->getChild() === $this) {
                $zeitblock->setChild(null);
            }
        }

        return $this;
    }

    public function getKind(): ?Kind
    {
        return $this->kind;
    }

    public function setKind(Kind $kind): self
    {
        $this->kind = $kind;

        return $this;
    }

    public function getWeight(): ?float
    {
        return $this->weight;
    }

    public function setWeight(float $weight): self
    {
        $this->weight = $weight;

        return $this;
    }
}
