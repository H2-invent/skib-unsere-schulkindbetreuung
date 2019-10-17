<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Validator\Constraints as Assert;



/**
 * @ORM\Entity(repositoryClass="App\Repository\KindRepository")
 */
class Kind
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Stammdaten", inversedBy="kinds")
     * @ORM\JoinColumn(nullable=false)
     */
    private $eltern;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $allergie;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $medikamente;

    /**
     * @Assert\NotBlank()
     * @ORM\Column(type="text", nullable=true)
     */
    private $vorname;

    /**
     * @Assert\NotBlank()
     * @ORM\Column(type="text", nullable=true)
     */
    private $nachname;

    /**
     * @Assert\NotBlank()
     * @ORM\Column(type="integer", nullable=true)
     */
    private $klasse;

    /**
     * @Assert\NotBlank()
     * @ORM\Column(type="integer", nullable=true)
     */
    private $art;

    /**
     * @Assert\NotBlank()
     * @ORM\Column(type="datetime")
     */
    private $geburtstag;



    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Abwesend", mappedBy="kind")
     */
    private $abwesends;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Zeitblock", mappedBy="kind")
     */
    private $zeitblocks;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $bemerkung;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Schule", inversedBy="kinder")
     * @ORM\JoinColumn(nullable=false)
     */
    private $schule;

    /**
     * @ORM\Column(type="boolean")
     */
    private $fin = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $gluten = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $laktose = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $schweinefleisch = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $vegetarisch = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $ausfluege = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $alleineHause = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $sonnencreme = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $fotos = false;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\zeitblock", inversedBy="kinderBeworben")
     */
    private $beworben;

    public function __construct()
    {
        $this->zeitblocks = new ArrayCollection();
        $this->abwesends = new ArrayCollection();
        $this->beworben = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEltern(): ?stammdaten
    {
        return $this->eltern;
    }

    public function setEltern(?stammdaten $eltern): self
    {
        $this->eltern = $eltern;

        return $this;
    }

    public function getAllergie(): ?string
    {
        return $this->allergie;
    }

    public function setAllergie(?string $allergie): self
    {
        $this->allergie = $allergie;

        return $this;
    }

    public function getMedikamente(): ?string
    {
        return $this->medikamente;
    }

    public function setMedikamente(?string $medikamente): self
    {
        $this->medikamente = $medikamente;

        return $this;
    }

    public function getVorname(): ?string
    {
        return $this->vorname;
    }

    public function setVorname(?string $vorname): self
    {
        $this->vorname = $vorname;

        return $this;
    }

    public function getNachname(): ?string
    {
        return $this->nachname;
    }

    public function setNachname(?string $nachname): self
    {
        $this->nachname = $nachname;

        return $this;
    }

    public function getKlasse(): ?int
    {
        return $this->klasse;
    }

    public function setKlasse(?int $klasse): self
    {
        $this->klasse = $klasse;

        return $this;
    }

    public function getArt(): ?int
    {
        return $this->art;
    }

    public function setArt(?int $art): self
    {
        $this->art = $art;

        return $this;
    }

    public function getGeburtstag(): ?\DateTimeInterface
    {
        return $this->geburtstag;
    }

    public function setGeburtstag(\DateTimeInterface $geburtstag): self
    {
        $this->geburtstag = $geburtstag;

        return $this;
    }

    /**
     * @return Collection|Zeitblock[]
     */
    public function getZeitblocks(): Collection
    {
        return $this->zeitblocks;
    }


    public function addZeitblock(Zeitblock $zeitblock): self
    {
        if (!$this->zeitblocks->contains($zeitblock)) {
            $this->zeitblocks[] = $zeitblock;
            $zeitblock->addKind($this);
        }

        return $this;
    }

    public function removeZeitblock(Zeitblock $zeitblock): self
    {
        if ($this->zeitblocks->contains($zeitblock)) {
            $this->zeitblocks->removeElement($zeitblock);
            $zeitblock->removeKind($this);
        }

        return $this;
    }

    /**
     * @return Collection|Abwesend[]
     */
    public function getAbwesends(): Collection
    {
        return $this->abwesends;
    }

    public function addAbwesend(Abwesend $abwesend): self
    {
        if (!$this->abwesends->contains($abwesend)) {
            $this->abwesends[] = $abwesend;
            $abwesend->setKind($this);
        }

        return $this;
    }

    public function removeAbwesend(Abwesend $abwesend): self
    {
        if ($this->abwesends->contains($abwesend)) {
            $this->abwesends->removeElement($abwesend);
            // set the owning side to null (unless already changed)
            if ($abwesend->getKind() === $this) {
                $abwesend->setKind(null);
            }
        }

        return $this;
    }

    public function getBemerkung(): ?string
    {
        return $this->bemerkung;
    }

    public function setBemerkung(?string $bemerkung): self
    {
        $this->bemerkung = $bemerkung;

        return $this;
    }

    public function getSchule(): ?Schule
    {
        return $this->schule;
    }

    public function setSchule(?Schule $schule): self
    {
        $this->schule = $schule;

        return $this;
    }
    public function getBetreungsblocks()
    {
        $blocks = $this->zeitblocks;
        $summe = 0;
        foreach ($blocks as $data){
            if($data->getGanztag()!= 0){
                $summe++;
            }
        }
        return $summe;
    }
    public function getTageWithBlocks(){

        $blocks2 = array();

        $blocks = $this->zeitblocks->toArray();
        $blocks = array_merge($blocks, $this->beworben->toArray());
        dump($blocks);
        foreach ($blocks as $data){
            if($data->getGanztag() != 0){
                $blocks2[$data->getWochentag()][] = $data;
            }
        }
        return sizeof($blocks2);
    }
    public function getBetreungsblocksReal()
{
    $blocks = $this->zeitblocks;
    $summe = array();
    foreach ($blocks as $data){
        if($data->getGanztag()!= 0){
            $summe[]=$data;
        }
    }
    return $summe;
}
    public function getBetreungsblocksRealKontingent()
    {
        $blocks = $this->beworben;
        $summe = array();
        foreach ($blocks as $data){
            if($data->getGanztag()!= 0){
                $summe[]=$data;
            }
        }
        return $summe;
    }
    public function getMittagessenblocksReal()
    {
        $blocks = $this->zeitblocks;
        $summe = array();
        foreach ($blocks as $data){
            if($data->getGanztag() == 0){
                $summe[]=$data;
            }
        }
        return $summe;
    }
        public function getPreisforBetreuung()
    {   // Load the data from the city into the controller as $stadt

        //Include Parents in this route
        $adresse = $this->getEltern();
        $kind = $this;
        $kinder = $adresse->getKinds()->toArray();


        usort(
            $kinder,
            function ($a, $b) {
                if ($a->getGeburtstag() == $b->getGeburtstag()) {
                    return 0;
                }

                return ($a->getGeburtstag() < $b->getGeburtstag()) ? -1 : 1;
            }
        );


        $blocks = $kind->getZeitblocks()->toArray();
        $blocks = array_merge($blocks, $this->beworben->toArray());




// Wenn weniger als zwei Blöcke für das Kind ausgewählt sind
        $summe = 0;
        $loop = 0;
        //$summe += $this->getBetragforKindMittagessen($kind, $adresse);

        foreach ($kinder as $data) {

            if ($kind == $data) {

                switch ($loop) {
                    case 0:
                        if ($adresse->getKinderImKiga()) {
                            $summe += $this->getBetragforKindBetreuung($kind, $adresse) * 0.75;
                        } else {
                            $summe += $this->getBetragforKindBetreuung($kind, $adresse);
                        }
                        break;
                    case 1:
                        $summe += $this->getBetragforKindBetreuung($kind, $adresse) * 0.5;
                        break;

                    default:
                        $summe += 0;
                        break;

                }
                break;
            }
            $loop++;
        }
        return $summe;
    }

    private function getBetragforKindBetreuung(Kind $kind,Stammdaten $eltern){
        $summe = 0;
        $blocks = $kind->getZeitblocks()->toArray();
        $blocks = array_merge($blocks, $kind->getBeworben()->toArray());
        dump($blocks);
        foreach ($blocks as $data){

            if($data->getGanztag() != 0 && $data->getDeleted() == false){
                $summe += $data->getPreise()[$eltern->getEinkommen()];
            }
        }
        return $summe;
    }
    private function getBetragforKindMittagessen(Kind $kind,Stammdaten $eltern){
        $summe = 0;
        foreach ($kind->getZeitblocks() as $data){
            if($data->getGanztag() == 0 && $eltern->getBuk() == false && $data->getDeleted() == false){
                $summe += $data->getPreise()[$eltern->getEinkommen()];
            }
        }
        return $summe;
    }

    public function getFin(): ?bool
    {
        return $this->fin;
    }

    public function setFin(bool $fin): self
    {
        $this->fin = $fin;

        return $this;
    }

    public function getGluten(): ?bool
    {
        return $this->gluten;
    }

    public function setGluten(bool $gluten): self
    {
        $this->gluten = $gluten;

        return $this;
    }

    public function getLaktose(): ?bool
    {
        return $this->laktose;
    }

    public function setLaktose(bool $laktose): self
    {
        $this->laktose = $laktose;

        return $this;
    }

    public function getSchweinefleisch(): ?bool
    {
        return $this->schweinefleisch;
    }

    public function setSchweinefleisch(bool $schweinefleisch): self
    {
        $this->schweinefleisch = $schweinefleisch;

        return $this;
    }

    public function getVegetarisch(): ?bool
    {
        return $this->vegetarisch;
    }

    public function setVegetarisch(bool $vegetarisch): self
    {
        $this->vegetarisch = $vegetarisch;

        return $this;
    }

    public function getAusfluege(): ?bool
    {
        return $this->ausfluege;
    }

    public function setAusfluege(bool $ausfluege): self
    {
        $this->ausfluege = $ausfluege;

        return $this;
    }

    public function getAlleineHause(): ?bool
    {
        return $this->alleineHause;
    }

    public function setAlleineHause(bool $alleineHause): self
    {
        $this->alleineHause = $alleineHause;

        return $this;
    }

    public function getSonnencreme(): ?bool
    {
        return $this->sonnencreme;
    }

    public function setSonnencreme(bool $sonnencreme): self
    {
        $this->sonnencreme = $sonnencreme;

        return $this;
    }

    public function getFotos(): ?bool
    {
        return $this->fotos;
    }

    public function setFotos(bool $fotos): self
    {
        $this->fotos = $fotos;

        return $this;
    }

    /**
     * @return Collection|zeitblock[]
     */
    public function getBeworben(): Collection
    {
        return $this->beworben;
    }

    public function addBeworben(zeitblock $beworben): self
    {
        if (!$this->beworben->contains($beworben)) {
            $this->beworben[] = $beworben;
        }

        return $this;
    }

    public function removeBeworben(zeitblock $beworben): self
    {
        if ($this->beworben->contains($beworben)) {
            $this->beworben->removeElement($beworben);
        }

        return $this;
    }
}
