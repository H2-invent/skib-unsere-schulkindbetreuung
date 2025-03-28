<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Repository\KundennummernRepository::class)]
class Kundennummern
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\JoinColumn(nullable: false)]
    #[ORM\ManyToOne(targetEntity: \App\Entity\organisation::class, inversedBy: 'kundennummerns')]
    private $organisation;

    #[ORM\JoinColumn(nullable: false)]
    #[ORM\ManyToOne(targetEntity: \App\Entity\stammdaten::class, inversedBy: 'kundennummerns')]
    private $stammdaten;

    #[ORM\Column(type: 'text')]
    private $kundennummer;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrganisation(): ?organisation
    {
        return $this->organisation;
    }

    public function setOrganisation(?organisation $organisation): self
    {
        $this->organisation = $organisation;

        return $this;
    }

    public function getStammdaten(): ?stammdaten
    {
        return $this->stammdaten;
    }

    public function setStammdaten(?stammdaten $stammdaten): self
    {
        $this->stammdaten = $stammdaten;

        return $this;
    }

    public function getKundennummer(): ?string
    {
        return $this->kundennummer;
    }

    public function setKundennummer(string $kundennummer): self
    {
        $this->kundennummer = $kundennummer;

        return $this;
    }
}
