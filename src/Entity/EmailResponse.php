<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EmailResponseRepository")
 */
class EmailResponse
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
    private $reciever;

    /**
     * @ORM\Column(type="integer")
     */
    private $status;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Stammdaten")
     */
    private $stammdaten;

    /**
     * @ORM\Column(type="boolean")
     */
    private $allert;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReciever(): ?string
    {
        return $this->reciever;
    }

    public function setReciever(string $reciever): self
    {
        $this->reciever = $reciever;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getStammdaten(): ?Stammdaten
    {
        return $this->stammdaten;
    }

    public function setStammdaten(?Stammdaten $stammdaten): self
    {
        $this->stammdaten = $stammdaten;

        return $this;
    }

    public function getAllert(): ?bool
    {
        return $this->allert;
    }

    public function setAllert(bool $allert): self
    {
        $this->allert = $allert;

        return $this;
    }
}
