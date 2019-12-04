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
     * @ORM\ManyToOne(targetEntity="App\Entity\Stadt", inversedBy="ferienblocks")
     * @ORM\JoinColumn(nullable=false)
     */
    private $stadt;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Organisation", inversedBy="ferienblocks")
     * @ORM\JoinColumn(nullable=false)
     */
    private $organisation;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $anzahlPreise;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $namePreise = [];

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\KindFerienblock", mappedBy="ferienblock")
     */
    private $kindFerienblocks;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $warteliste;

    /**
     * @ORM\Column(type="boolean")
     */
    private $modeMaximal;

    public function __construct()
    {
        $this->kinder = new ArrayCollection();
        $this->kinderGebucht = new ArrayCollection();
        $this->kinderBezahlt = new ArrayCollection();
        $this->kinderStorniert = new ArrayCollection();
        $this->kindFerienblocks = new ArrayCollection();
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

    public function getAnzahlPreise(): ?int
    {
        return $this->anzahlPreise;
    }

    public function setAnzahlPreise(?int $anzahlPreise): self
    {
        $this->anzahlPreise = $anzahlPreise;

        return $this;
    }

    public function getNamePreise(): ?array
    {
        return $this->namePreise;
    }

    public function setNamePreise(?array $namePreise): self
    {
        $this->namePreise = $namePreise;

        return $this;
    }

    /**
     * @return Collection|KindFerienblock[]
     */
    public function getKindFerienblocks(): Collection
    {
        return $this->kindFerienblocks;
    }

    public function addKindFerienblock(KindFerienblock $kindFerienblock): self
    {
        if (!$this->kindFerienblocks->contains($kindFerienblock)) {
            $this->kindFerienblocks[] = $kindFerienblock;
            $kindFerienblock->setFerienblock($this);
        }

        return $this;
    }

    public function removeKindFerienblock(KindFerienblock $kindFerienblock): self
    {
        if ($this->kindFerienblocks->contains($kindFerienblock)) {
            $this->kindFerienblocks->removeElement($kindFerienblock);
            // set the owning side to null (unless already changed)
            if ($kindFerienblock->getFerienblock() === $this) {
                $kindFerienblock->setFerienblock(null);
            }
        }

        return $this;
    }
    public function getKindFerienblocksBeworben(): Collection
    {
        $res = array();
        $ferienblock = $this->kindFerienblocks->toArray();
        foreach ($ferienblock as $data){
            if($data->getState() == 0){
                $res[] = $data;
            }
        }
        return new ArrayCollection($res);
    }
    /**
     * @return Collection|KindFerienblock[]
     */
    public function getKindFerienblocksGebucht(): Collection
    {
        $res = array();
        $ferienblock = $this->kindFerienblocks->toArray();
        foreach ($ferienblock as $data){
            if($data->getState() == 10){
                $res[] = $data;
            }
        }
        return new ArrayCollection($res);
    }
    /**
     * @return Collection|KindFerienblock[]
     */
    public function getKindFerienblocksWarteliste(): Collection
    {
        $res = array();
        $ferienblock = $this->kindFerienblocks->toArray();
        foreach ($ferienblock as $data){
            if($data->getState() == 15){
                $res[] = $data;
            }
        }
        return new ArrayCollection($res);
    }
    /**
     * @return Collection|KindFerienblock[]
     */
    public function getKindFerienblocksStorniert(): Collection
    {
        $res = array();
        $ferienblock = $this->kindFerienblocks->toArray();
        foreach ($ferienblock as $data){
            if($data->getState() == 20){
                $res[] = $data;
            }
        }
        return new ArrayCollection($res);
    }
    /**
     * @return Collection|KindFerienblock[]
     */
    public function getKindFerienblocksBezahlt(): Collection
    {
        $res = array();
        $ferienblock = $this->kindFerienblocks->toArray();
        foreach ($ferienblock as $data){
            if($data->getBezahlt() === true){
                $res[] = $data;
            }
        }
        return new ArrayCollection($res);
    }
    /**
     * @return Collection|KindFerienblock[]
     */
    public function getKindFerienblocksNichtBezahlt(): Collection
    {
        $res = array();
        $ferienblock = $this->kindFerienblocks->toArray();
        foreach ($ferienblock as $data){
            if($data->getBezahlt() === false){
                $res[] = $data;
            }
        }
        return new ArrayCollection($res);
    }

    public function getWarteliste(): ?bool
    {
        return $this->warteliste;
    }

    public function setWarteliste(bool $warteliste): self
    {
        $this->warteliste = $warteliste;

        return $this;
    }

    public function getModeMaximal(): ?bool
    {
        return $this->modeMaximal;
    }

    public function setModeMaximal(bool $modeMaximal): self
    {
        $this->modeMaximal = $modeMaximal;

        return $this;
    }
}
