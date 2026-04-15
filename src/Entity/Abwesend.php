<?php

namespace App\Entity;

use App\Repository\AbwesendRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AbwesendRepository::class)]
class Abwesend
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;



    #[ORM\JoinColumn(nullable: false)]
    #[ORM\ManyToOne(targetEntity: kind::class, inversedBy: 'abwesends')]
    private $kind;

    #[ORM\Column(type: 'datetime')]
    private $von;

    #[ORM\Column(type: 'datetime')]
    private $bis;

    #[ORM\JoinColumn(nullable: false)]
    #[ORM\ManyToOne(targetEntity: Zeitblock::class, inversedBy: 'abwesenheit')]
    private $zeitblock;



    public function __construct()
    {
        $this->zeitblock = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }


    public function getKind(): ?kind
    {
        return $this->kind;
    }

    public function setKind(?kind $kind): self
    {
        $this->kind = $kind;

        return $this;
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

    public function getZeitblock(): ?zeitblock
    {
        return $this->zeitblock;
    }

    public function setZeitblock(?zeitblock $zeitblock): self
    {
        $this->zeitblock = $zeitblock;

        return $this;
    }
}
