<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AnmeldefristenRepository")
 */
class Anmeldefristen
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Stadt", inversedBy="anmeldefristens")
     * @ORM\JoinColumn(nullable=false)
     */
    private $stadt;

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

    public function getStadt(): ?stadt
    {
        return $this->stadt;
    }

    public function setStadt(?stadt $stadt): self
    {
        $this->stadt = $stadt;

        return $this;
    }
}
