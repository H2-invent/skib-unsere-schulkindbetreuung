<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ActiveRepository")
 */
class Active
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $von;

    /**
     * @ORM\Column(type="datetime")
     */
    private $bis;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\zeitblock", inversedBy="active", cascade={"persist", "remove"})
     */
    private $zeitblock;



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
