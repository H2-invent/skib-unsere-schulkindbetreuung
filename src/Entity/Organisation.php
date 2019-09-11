<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\OrganisationRepository")
 */
class Organisation
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
     * @ORM\OneToMany(targetEntity="App\Entity\schule", mappedBy="organisation")
     */
    private $schule;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\stadt", inversedBy="organisations")
     */
    private $stadt;

    public function __construct()
    {
        $this->schule = new ArrayCollection();
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

    /**
     * @return Collection|schule[]
     */
    public function getSchule(): Collection
    {
        return $this->schule;
    }

    public function addSchule(schule $schule): self
    {
        if (!$this->schule->contains($schule)) {
            $this->schule[] = $schule;
            $schule->setOrganisation($this);
        }

        return $this;
    }

    public function removeSchule(schule $schule): self
    {
        if ($this->schule->contains($schule)) {
            $this->schule->removeElement($schule);
            // set the owning side to null (unless already changed)
            if ($schule->getOrganisation() === $this) {
                $schule->setOrganisation(null);
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
