<?php

namespace App\Entity;

use App\Repository\AutoBlockAssignmentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AutoBlockAssignmentRepository::class)]
class AutoBlockAssignment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'autoBlockAssignment')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Organisation $organisation = null;

    #[ORM\OneToMany(mappedBy: 'autoBlockAssignment', targetEntity: AutoBlockAssignmentChild::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $children;

    public function __construct()
    {
        $this->children = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrganisation(): ?Organisation
    {
        return $this->organisation;
    }

    public function setOrganisation(Organisation $organisation): self
    {
        $this->organisation = $organisation;

        return $this;
    }

    /**
     * @return Collection<int, AutoBlockAssignmentChild>
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function addChild(AutoBlockAssignmentChild $child): self
    {
        if (!$this->children->contains($child)) {
            $this->children->add($child);
            $child->setAutoBlockAssignment($this);
        }

        return $this;
    }

    public function removeChild(AutoBlockAssignmentChild $child): self
    {
        if ($this->children->removeElement($child)) {
            // set the owning side to null (unless already changed)
            if ($child->getAutoBlockAssignment() === $this) {
                $child->setAutoBlockAssignment(null);
            }
        }

        return $this;
    }
}
