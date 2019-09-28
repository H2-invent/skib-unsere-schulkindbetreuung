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
     * @ORM\ManyToOne(targetEntity="App\Entity\Stadt", inversedBy="actives")
     * @ORM\JoinColumn(nullable=false)
     */
    private $stadt;

    /**
     * @ORM\Column(type="datetime")
     */
    private $anmeldeStart;

    /**
     * @ORM\Column(type="datetime")
     */
    private $anmeldeEnde;






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

    public function getStadt(): ?Stadt
    {
        return $this->stadt;
    }

    public function setStadt(?Stadt $stadt): self
    {
        $this->stadt = $stadt;

        return $this;
    }

    public function getAnmeldeStart(): ?\DateTimeInterface
    {
        return $this->anmeldeStart;
    }

    public function setAnmeldeStart(\DateTimeInterface $anmeldeStart): self
    {
        $this->anmeldeStart = $anmeldeStart;

        return $this;
    }

    public function getAnmeldeEnde(): ?\DateTimeInterface
    {
        return $this->anmeldeEnde;
    }

    public function setAnmeldeEnde(\DateTimeInterface $anmeldeEnde): self
    {
        $this->anmeldeEnde = $anmeldeEnde;

        return $this;
    }



}
