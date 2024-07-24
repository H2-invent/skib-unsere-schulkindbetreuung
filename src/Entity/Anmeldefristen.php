<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Repository\AnmeldefristenRepository::class)]
class Anmeldefristen
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'datetime')]
    private $von;

    #[ORM\Column(type: 'datetime')]
    private $bis;

    #[ORM\JoinColumn(nullable: false)]
    #[ORM\ManyToOne(targetEntity: \App\Entity\Stadt::class, inversedBy: 'anmeldefristens')]
    private $stadt;

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

    public function getStadt(): ?Stadt
    {
        return $this->stadt;
    }

    public function setStadt(?Stadt $stadt): self
    {
        $this->stadt = $stadt;

        return $this;
    }
}
