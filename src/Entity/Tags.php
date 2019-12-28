<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TagsRepository")
 */
class Tags
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
     * @ORM\ManyToMany(targetEntity="App\Entity\Ferienblock", mappedBy="kategorie")
     */
    private $feriens;



    public function __construct()
    {
        $this->feriens = new ArrayCollection();
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
     * @return Collection|Ferienblock[]
     */
    public function getFeriens(): Collection
    {
        return $this->feriens;
    }

    public function addFerien(Ferienblock $ferien): self
    {
        if (!$this->feriens->contains($ferien)) {
            $this->feriens[] = $ferien;
            $ferien->addKategorie($this);
        }

        return $this;
    }

    public function removeFerien(Ferienblock $ferien): self
    {
        if ($this->feriens->contains($ferien)) {
            $this->feriens->removeElement($ferien);
            $ferien->removeKategorie($this);
        }

        return $this;
    }

   

}
