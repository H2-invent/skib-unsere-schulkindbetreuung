<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\KinderRechnungRepository;
use Doctrine\ORM\Mapping as ORM;
use function number_format;
use function round;

#[ORM\Entity(repositoryClass: KinderRechnungRepository::class)]
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
        $this->summe = number_format(round($summe, 2), 2, '.', '');

        return $this;
    }
}
