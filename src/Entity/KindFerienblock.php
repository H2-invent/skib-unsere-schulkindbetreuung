<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\KindFerienblockRepository")
 */
class KindFerienblock
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Kind", inversedBy="kindFerienblocks")
     * @ORM\JoinColumn(nullable=false)
     */
    private $kind;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Ferienblock", inversedBy="kindFerienblocks")
     * @ORM\JoinColumn(nullable=false)
     */
    private $ferienblock;

    /**
     * @ORM\Column(type="float")
     */
    private $preis;

    /**
     * @ORM\Column(type="integer")
     */
    private $state;

    /**
     * @ORM\Column(type="boolean")
     */
    private $bezahlt = false;

    /**
     * @ORM\Column(type="text")
     */
    private $checkinID;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $checkinStatus = [];

    /**
     * @ORM\Column(type="integer")
     */
    private $preisId;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $markedAsStorno;

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

    public function getFerienblock(): ?Ferienblock
    {
        return $this->ferienblock;
    }

    public function setFerienblock(?Ferienblock $ferienblock): self
    {
        $this->ferienblock = $ferienblock;

        return $this;
    }

    public function getPreis(): ?float
    {
        return $this->preis;
    }

    public function setPreis(float $preis): self
    {
        $this->preis = $preis;

        return $this;
    }

    public function getState(): ?int
    {
        return $this->state;
    }

    public function setState(int $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function getBezahlt(): ?bool
    {
        return $this->bezahlt;
    }

    public function setBezahlt(bool $bezahlt): self
    {
        $this->bezahlt = $bezahlt;

        return $this;
    }

    public function getCheckinID(): ?string
    {
        return $this->checkinID;
    }

    public function setCheckinID(string $checkinID): self
    {
        $this->checkinID = $checkinID;

        return $this;
    }

    public function getCheckinStatus(): ?array
    {
        return $this->checkinStatus;
    }

    public function setCheckinStatus(?array $checkinStatus): self
    {
        $this->checkinStatus = $checkinStatus;

        return $this;
    }

    public function getPreisId(): ?int
    {
        return $this->preisId;
    }

    public function setPreisId(int $preisId): self
    {
        $this->preisId = $preisId;

        return $this;
    }

    public function getMarkedAsStorno(): ?bool
    {
        return $this->markedAsStorno;
    }

    public function setMarkedAsStorno(?bool $markedAsStorno): self
    {
        $this->markedAsStorno = $markedAsStorno;

        return $this;
    }

}
