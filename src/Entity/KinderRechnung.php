<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'kinder_rechnung', uniqueConstraints: [
    new ORM\UniqueConstraint(name: 'uniq_kinder_rechnung_kind', columns: ['rechnung_id', 'kind_id']),
])]
class KinderRechnung
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Rechnung::class, inversedBy: 'kinderRechnungen')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Rechnung $rechnung = null;

    #[ORM\ManyToOne(targetEntity: Kind::class, inversedBy: 'kinderRechnungen')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Kind $kind = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private string $summe = '0.00';

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private string $bruttoSumme = '0.00';

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private string $rabatt = '0.00';

    public function getId(): ?int
    {
        return $this->id;
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

    public function getKind(): ?Kind
    {
        return $this->kind;
    }

    public function setKind(Kind $kind): self
    {
        $this->kind = $kind;
        if (!$kind->getKinderRechnungen()->contains($this)) {
            $kind->addKinderRechnung($this);
        }

        return $this;
    }

    public function getSumme(): float
    {
        return (float) $this->summe;
    }

    public function setSumme(float $summe): self
    {
        $this->summe = $this->money($summe);

        return $this;
    }

    public function getBruttoSumme(): float
    {
        return (float) $this->bruttoSumme;
    }

    public function setBruttoSumme(float $bruttoSumme): self
    {
        $this->bruttoSumme = $this->money($bruttoSumme);

        return $this;
    }

    public function getRabatt(): float
    {
        return (float) $this->rabatt;
    }

    public function setRabatt(float $rabatt): self
    {
        $this->rabatt = $this->money($rabatt);

        return $this;
    }

    private function money(float $amount): string
    {
        return number_format(round($amount, 2), 2, '.', '');
    }
}
