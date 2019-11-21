<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model\Translatable\Translatable as Translatable;
use Symfony\Component\Validator\Constraints as Assert;
/**
 * @ORM\Entity(repositoryClass="App\Repository\FerienblockRepository")
 */
class Ferienblock
{
    use Translatable;
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
      * @Assert\NotBlank()
     */
    private $minAlter;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $maxAlter;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\NotBlank()
     */
    private $startDate;

    /**
     * @ORM\Column(type="time")
     * @Assert\NotBlank()
     */
    private $StartTime;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\NotBlank()
     */
    private $endDate;

    /**
     * @ORM\Column(type="time")
     * @Assert\NotBlank()
     */
    private $endTime;

    /**
     * @ORM\Column(type="date")
     * @Assert\NotBlank()
     */
    private $endVerkauf;

    /**
     * @ORM\Column(type="date")
     * @Assert\NotBlank()
     */
    private $startVerkauf;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $minAnzahl;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $maxAnzahl;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     */
    private $Ort;

    /**
     * @ORM\Column(type="json")
     */
    private $preis = [];

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Kind", mappedBy="ferienProgrammBeworben")
     */
    private $kinder;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Kind", mappedBy="ferienProgrammGebucht")
     */
    private $kinderGebucht;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Kind", mappedBy="ferienProgrammBezahlt")
     */
    private $kinderBezahlt;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Kind", mappedBy="ferienProgrammStorniert")
     */
    private $kinderStorniert;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Stadt", inversedBy="ferienblocks")
     * @ORM\JoinColumn(nullable=false)
     */
    private $stadt;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Organisation", inversedBy="ferienblocks")
     * @ORM\JoinColumn(nullable=false)
     */
    private $organisation;

    public function __construct()
    {
        $this->kinder = new ArrayCollection();
        $this->kinderGebucht = new ArrayCollection();
        $this->kinderBezahlt = new ArrayCollection();
        $this->kinderStorniert = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMinAlter(): ?int
    {
        return $this->minAlter;
    }

    public function setMinAlter(int $minAlter): self
    {
        $this->minAlter = $minAlter;

        return $this;
    }

    public function getMaxAlter(): ?int
    {
        return $this->maxAlter;
    }

    public function setMaxAlter(?int $maxAlter): self
    {
        $this->maxAlter = $maxAlter;

        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getStartTime(): ?\DateTimeInterface
    {
        return $this->StartTime;
    }

    public function setStartTime(\DateTimeInterface $StartTime): self
    {
        $this->StartTime = $StartTime;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getEndTime(): ?\DateTimeInterface
    {
        return $this->endTime;
    }

    public function setEndTime(\DateTimeInterface $endTime): self
    {
        $this->endTime = $endTime;

        return $this;
    }

    public function getEndVerkauf(): ?\DateTimeInterface
    {
        return $this->endVerkauf;
    }

    public function setEndVerkauf(\DateTimeInterface $endVerkauf): self
    {
        $this->endVerkauf = $endVerkauf;

        return $this;
    }

    public function getStartVerkauf(): ?\DateTimeInterface
    {
        return $this->startVerkauf;
    }

    public function setStartVerkauf(\DateTimeInterface $startVerkauf): self
    {
        $this->startVerkauf = $startVerkauf;

        return $this;
    }

    public function getMinAnzahl(): ?int
    {
        return $this->minAnzahl;
    }

    public function setMinAnzahl(?int $minAnzahl): self
    {
        $this->minAnzahl = $minAnzahl;

        return $this;
    }

    public function getMaxAnzahl(): ?int
    {
        return $this->maxAnzahl;
    }

    public function setMaxAnzahl(?int $maxAnzahl): self
    {
        $this->maxAnzahl = $maxAnzahl;

        return $this;
    }

    public function getOrt(): ?string
    {
        return $this->Ort;
    }

    public function setOrt(string $Ort): self
    {
        $this->Ort = $Ort;

        return $this;
    }

    public function getPreis(): ?array
    {
        return $this->preis;
    }

    public function setPreis(array $preis): self
    {
        $this->preis = $preis;

        return $this;
    }

    /**
     * @return Collection|Kind[]
     */
    public function getKinder(): Collection
    {
        return $this->kinder;
    }

    public function addKinder(Kind $kinder): self
    {
        if (!$this->kinder->contains($kinder)) {
            $this->kinder[] = $kinder;
            $kinder->addFerienProgrammBeworben($this);
        }

        return $this;
    }

    public function removeKinder(Kind $kinder): self
    {
        if ($this->kinder->contains($kinder)) {
            $this->kinder->removeElement($kinder);
            $kinder->removeFerienProgrammBeworben($this);
        }

        return $this;
    }

    /**
     * @return Collection|Kind[]
     */
    public function getKinderGebucht(): Collection
    {
        return $this->kinderGebucht;
    }

    public function addKinderGebucht(Kind $kinderGebucht): self
    {
        if (!$this->kinderGebucht->contains($kinderGebucht)) {
            $this->kinderGebucht[] = $kinderGebucht;
            $kinderGebucht->addFerienProgrammGebucht($this);
        }

        return $this;
    }

    public function removeKinderGebucht(Kind $kinderGebucht): self
    {
        if ($this->kinderGebucht->contains($kinderGebucht)) {
            $this->kinderGebucht->removeElement($kinderGebucht);
            $kinderGebucht->removeFerienProgrammGebucht($this);
        }

        return $this;
    }

    /**
     * @return Collection|Kind[]
     */
    public function getKinderBezahlt(): Collection
    {
        return $this->kinderBezahlt;
    }

    public function addKinderBezahlt(Kind $kinderBezahlt): self
    {
        if (!$this->kinderBezahlt->contains($kinderBezahlt)) {
            $this->kinderBezahlt[] = $kinderBezahlt;
            $kinderBezahlt->addKinderBezahlt($this);
        }

        return $this;
    }

    public function removeKinderBezahlt(Kind $kinderBezahlt): self
    {
        if ($this->kinderBezahlt->contains($kinderBezahlt)) {
            $this->kinderBezahlt->removeElement($kinderBezahlt);
            $kinderBezahlt->removeKinderBezahlt($this);
        }

        return $this;
    }

    /**
     * @return Collection|Kind[]
     */
    public function getKinderStorniert(): Collection
    {
        return $this->kinderStorniert;
    }

    public function addKinderStorniert(Kind $kinderStorniert): self
    {
        if (!$this->kinderStorniert->contains($kinderStorniert)) {
            $this->kinderStorniert[] = $kinderStorniert;
            $kinderStorniert->addFerienBlockStorniert($this);
        }

        return $this;
    }

    public function removeKinderStorniert(Kind $kinderStorniert): self
    {
        if ($this->kinderStorniert->contains($kinderStorniert)) {
            $this->kinderStorniert->removeElement($kinderStorniert);
            $kinderStorniert->removeFerienBlockStorniert($this);
        }

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

    public function getOrganisation(): ?Organisation
    {
        return $this->organisation;
    }

    public function setOrganisation(?Organisation $organisation): self
    {
        $this->organisation = $organisation;

        return $this;
    }
}
