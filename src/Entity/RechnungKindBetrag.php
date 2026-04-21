<?php

namespace App\Entity;

use App\Repository\RechnungKindBetragRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RechnungKindBetragRepository::class)]
class RechnungKindBetrag
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'rechnungKindBetrags')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Kind $kind = null;

    #[ORM\ManyToOne(inversedBy: 'rechnungKindBetrags')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Rechnung $rechnung = null;

    #[ORM\Column]
    private ?float $betrag = null;

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

    public function getRechnung(): ?Rechnung
    {
        return $this->rechnung;
    }

    public function setRechnung(?Rechnung $rechnung): self
    {
        $this->rechnung = $rechnung;

        return $this;
    }

    public function getBetrag(): ?float
    {
        return $this->betrag;
    }

    public function setBetrag(float $betrag): self
    {
        $this->betrag = $betrag;

        return $this;
    }
}
