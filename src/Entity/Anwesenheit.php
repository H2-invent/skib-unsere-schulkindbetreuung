<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Repository\AnwesenheitRepository::class)]
class Anwesenheit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\JoinColumn(nullable: false)]
    #[ORM\ManyToOne(targetEntity: \App\Entity\Kind::class, inversedBy: 'anwesenheitenSchulkindbetreuung')]
    private $kind;

    #[ORM\Column(type: 'datetime')]
    private $createdAt;

    #[ORM\Column(type: 'datetime')]
    private $arrivedAt;

    #[ORM\JoinColumn(nullable: false)]
    #[ORM\ManyToOne(targetEntity: \App\Entity\Organisation::class, inversedBy: 'anwesenheitSchulkindbetreuung')]
    private $organisation;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getKind(): ?Kind
    {
        return $this->kind;
    }

    public function setKind(?Kind $kind): self
    {
        $this->kind = $kind;

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

    public function getArrivedAt(): ?\DateTimeInterface
    {
        return $this->arrivedAt;
    }

    public function setArrivedAt(\DateTimeInterface $arrivedAt): self
    {
        $this->arrivedAt = $arrivedAt;

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
}
