<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\StadtRepository")
 */
class Stadt
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
    private $slug;

    /**
     * @ORM\Column(type="text")
     */
    private $Name;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Anmeldefristen", mappedBy="stadt")
     */
    private $anmeldefristens;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Organisation", mappedBy="stadt")
     */
    private $organisations;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Schule", mappedBy="stadt")
     */
    private $schules;

    /**
     * @ORM\Column(type="datetime")
     */
    private $created_at;

    /**
     * @ORM\Column(type="boolean")
     */
    private $deleted = false;

    public function __construct()
    {
        $this->anmeldefristens = new ArrayCollection();
        $this->organisations = new ArrayCollection();
        $this->schules = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->Name;
    }

    public function setName(string $Name): self
    {
        $this->Name = $Name;

        return $this;
    }

    /**
     * @return Collection|Anmeldefristen[]
     */
    public function getAnmeldefristens(): Collection
    {
        return $this->anmeldefristens;
    }

    public function addAnmeldefristen(Anmeldefristen $anmeldefristen): self
    {
        if (!$this->anmeldefristens->contains($anmeldefristen)) {
            $this->anmeldefristens[] = $anmeldefristen;
            $anmeldefristen->setStadt($this);
        }

        return $this;
    }

    public function removeAnmeldefristen(Anmeldefristen $anmeldefristen): self
    {
        if ($this->anmeldefristens->contains($anmeldefristen)) {
            $this->anmeldefristens->removeElement($anmeldefristen);
            // set the owning side to null (unless already changed)
            if ($anmeldefristen->getStadt() === $this) {
                $anmeldefristen->setStadt(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Organisation[]
     */
    public function getOrganisations(): Collection
    {
        return $this->organisations;
    }

    public function addOrganisation(Organisation $organisation): self
    {
        if (!$this->organisations->contains($organisation)) {
            $this->organisations[] = $organisation;
            $organisation->setStadt($this);
        }

        return $this;
    }

    public function removeOrganisation(Organisation $organisation): self
    {
        if ($this->organisations->contains($organisation)) {
            $this->organisations->removeElement($organisation);
            // set the owning side to null (unless already changed)
            if ($organisation->getStadt() === $this) {
                $organisation->setStadt(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Schule[]
     */
    public function getSchules(): Collection
    {
        return $this->schules;
    }

    public function addSchule(Schule $schule): self
    {
        if (!$this->schules->contains($schule)) {
            $this->schules[] = $schule;
            $schule->setStadt($this);
        }

        return $this;
    }

    public function removeSchule(Schule $schule): self
    {
        if ($this->schules->contains($schule)) {
            $this->schules->removeElement($schule);
            // set the owning side to null (unless already changed)
            if ($schule->getStadt() === $this) {
                $schule->setStadt(null);
            }
        }

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

    public function getDeleted(): ?bool
    {
        return $this->deleted;
    }

    public function setDeleted(bool $deleted): self
    {
        $this->deleted = $deleted;

        return $this;
    }
}
