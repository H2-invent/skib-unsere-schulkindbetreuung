<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Repository\FAQRepository::class)]
class FAQ
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'text')]
    private $frage;

    #[ORM\Column(type: 'text')]
    private $antwort;

    #[ORM\Column(type: 'boolean')]
    private $active;

    #[ORM\Column(type: 'datetime')]
    private $createdAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFrage(): ?string
    {
        return $this->frage;
    }

    public function setFrage(string $frage): self
    {
        $this->frage = $frage;

        return $this;
    }

    public function getAntwort(): ?string
    {
        return $this->antwort;
    }

    public function setAntwort(string $antwort): self
    {
        $this->antwort = $antwort;

        return $this;
    }

    public function getActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
