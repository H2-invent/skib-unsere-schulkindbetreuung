<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity(repositoryClass="App\Repository\SepaRepository")
 */
class Sepa
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
    private $createdAt;

    /**
     * @ORM\Column(type="float")
     */
    private $summe;

    /**
     * @ORM\Column(type="integer")
     */
    private $anzahl;

    /**
     * @ORM\Column(type="text")
     */
    private $sepaXML;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Organisation", inversedBy="sepas")
     * @ORM\JoinColumn(nullable=false)
     */
    private $organisation;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\NotBlank()
     */
    private $von;

    /**
     * @ORM\Column(type="datetime")
     */
    private $bis;

    /**
     * @ORM\Column(type="blob")
     */
    private $pdf;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Rechnung", mappedBy="sepa",cascade={"persist"})
     */
    private $rechnungen;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\NotBlank()
     */
    private $einzugsDatum;

    public function __construct()
    {
        $this->rechnungen = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getSumme(): ?float
    {
        return $this->summe;
    }

    public function setSumme(float $summe): self
    {
        $this->summe = $summe;

        return $this;
    }

    public function getAnzahl(): ?int
    {
        return $this->anzahl;
    }

    public function setAnzahl(int $anzahl): self
    {
        $this->anzahl = $anzahl;

        return $this;
    }

    public function getSepaXML(): ?string
    {
        return $this->sepaXML;
    }

    public function setSepaXML(string $sepaXML): self
    {
        $this->sepaXML = $sepaXML;

        return $this;
    }

    public function getOrganisation(): ?Organisation
    {
        return $this->organisation;
    }

    public function setOrganisation(?Organisation $organisation): self
    {
        $this->organisation = $organisation;

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

    public function getPdf()
    {
        return $this->pdf;
    }

    public function setPdf($pdf): self
    {
        $this->pdf = $pdf;

        return $this;
    }

    /**
     * @return Collection|Rechnung[]
     */
    public function getRechnungen(): Collection
    {
        return $this->rechnungen;
    }

    public function addRechnungen(Rechnung $rechnungen): self
    {
        if (!$this->rechnungen->contains($rechnungen)) {
            $this->rechnungen[] = $rechnungen;
            $rechnungen->setSepa($this);
        }

        return $this;
    }

    public function removeRechnungen(Rechnung $rechnungen): self
    {
        if ($this->rechnungen->contains($rechnungen)) {
            $this->rechnungen->removeElement($rechnungen);
            // set the owning side to null (unless already changed)
            if ($rechnungen->getSepa() === $this) {
                $rechnungen->setSepa(null);
            }
        }

        return $this;
    }

    public function getEinzugsDatum(): ?\DateTimeInterface
    {
        return $this->einzugsDatum;
    }

    public function setEinzugsDatum(\DateTimeInterface $einzugsDatum): self
    {
        $this->einzugsDatum = $einzugsDatum;

        return $this;
    }
}
