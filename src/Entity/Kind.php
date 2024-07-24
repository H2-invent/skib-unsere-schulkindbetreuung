<?php

namespace App\Entity;

use ContainerM8BJPEV\getDoctrine_Orm_DefaultEntityManager_PropertyInfoExtractorService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Migrations\Configuration\EntityManager\ExistingEntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Table]
#[ORM\Index(name: 'idx_tracing', columns: ['tracing'])]
#[ORM\Entity(repositoryClass: \App\Repository\KindRepository::class)]
class Kind
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\JoinColumn(nullable: false)]
    #[ORM\ManyToOne(targetEntity: \App\Entity\Stammdaten::class, inversedBy: 'kinds')]
    private $eltern;

    #[ORM\Column(type: 'text', nullable: true)]
    private $allergie;

    #[ORM\Column(type: 'text', nullable: true)]
    private $medikamente;

    #[Assert\NotBlank]
    #[ORM\Column(type: 'text', nullable: true)]
    private $vorname;

    #[Assert\NotBlank]
    #[ORM\Column(type: 'text', nullable: true)]
    private $nachname;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $klasse;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $art;

    #[Assert\NotBlank]
    #[ORM\Column(type: 'datetime')]
    private $geburtstag;


    #[ORM\OneToMany(targetEntity: \App\Entity\Abwesend::class, mappedBy: 'kind')]
    private $abwesends;

    #[ORM\ManyToMany(targetEntity: \App\Entity\Zeitblock::class, mappedBy: 'kind')]
    private $zeitblocks;

    #[ORM\Column(type: 'text', nullable: true)]
    private $bemerkung;

    #[ORM\JoinColumn(nullable: true)]
    #[ORM\ManyToOne(targetEntity: \App\Entity\Schule::class, inversedBy: 'kinder')]
    private $schule;

    #[ORM\Column(type: 'boolean')]
    private $fin = false;

    #[ORM\Column(type: 'boolean')]
    private $gluten = false;

    #[ORM\Column(type: 'boolean')]
    private $laktose = false;

    #[ORM\Column(type: 'boolean')]
    private $schweinefleisch = false;

    #[ORM\Column(type: 'boolean')]
    private $vegetarisch = false;

    #[ORM\Column(type: 'boolean')]
    private $ausfluege = false;

    #[ORM\Column(type: 'boolean')]
    private $alleineHause = false;

    #[ORM\Column(type: 'boolean')]
    private $sonnencreme = false;

    #[ORM\Column(type: 'boolean')]
    private $fotos = false;

    #[ORM\ManyToMany(targetEntity: \App\Entity\Zeitblock::class, inversedBy: 'kinderBeworben')]
    private $beworben;

    #[ORM\Column(type: 'boolean')]
    private $saved = false;

    #[ORM\Column(type: 'integer')]
    private $history = 0;

    #[ORM\Column(type: 'string', nullable: true)]
    private $tracing;

    #[ORM\ManyToMany(targetEntity: \App\Entity\Rechnung::class, mappedBy: 'kinder')]
    private $rechnungen;


    #[ORM\OneToMany(targetEntity: \App\Entity\KindFerienblock::class, mappedBy: 'kind', cascade: ['remove'])]
    private $kindFerienblocks;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private $masernImpfung;

    #[ORM\OneToMany(targetEntity: \App\Entity\Anwesenheit::class, mappedBy: 'kind')]
    private $anwesenheitenSchulkindbetreuung;

    #[ORM\Column(type: 'text', nullable: true)]
    private $internalNotice;

    #[ORM\Column(type: 'date', nullable: true)]
    private $startDate;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private $zeckenEntfernen;


    public function __serialize(): array
    {
        return $this->tracing;
    }
    public function __toString()
    {
       return $this->tracing;
    }

    public function __construct()
    {
        $this->zeitblocks = new ArrayCollection();
        $this->abwesends = new ArrayCollection();
        $this->beworben = new ArrayCollection();
        $this->rechnungen = new ArrayCollection();
        $this->ferienProgrammBeworben = new ArrayCollection();
        $this->ferienProgrammGebucht = new ArrayCollection();
        $this->ferienProgrammBezahlt = new ArrayCollection();
        $this->ferienProgrammStorniert = new ArrayCollection();
        $this->kindFerienblocks = new ArrayCollection();
        $this->anwesenheitenSchulkindbetreuung = new ArrayCollection();

    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEltern(): ?Stammdaten
    {
            return $this->eltern;
    }

    public function setEltern(?Stammdaten $eltern): self
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

    public function getKlasseString(): ?string
    {
        $klassArr = $this->schule->getStadt()->translate()->getSettingsSkibShoolyearNamingArray();
        try {
            return $klassArr[$this->klasse];
        } catch (\Exception $exception) {
            return 'error! Contact the Administrator';
        }

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

    /**
     * @return Collection|Zeitblock[]
     */
    public function getRealZeitblocks(): Collection
    {
        $block = array();
        foreach ($this->zeitblocks->toArray() as $data) {
            if (!$data->getDeleted()) {
                $block[] = $data;
            }
        }

        usort($block, function (Zeitblock $a, Zeitblock $b) {
            return ($a->getVon() > $b->getVon() ? true : false);
        });

        return new ArrayCollection($block);
    }

    /**
     * @return Collection|Zeitblock[]
     */
    public function getRealBeworben(): Collection
    {
        $block = array();
        foreach ($this->beworben->toArray() as $data) {
            if (!$data->getDeleted()) {
                $block[] = $data;
            }
        }

        usort($block, function (Zeitblock $a, Zeitblock $b) {
            return ($a->getVon() > $b->getVon() ? true : false);
        });

        return new ArrayCollection($block);
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
        foreach ($blocks as $data) {
            if ($data->getGanztag() != 0) {
                $summe++;
            }
        }
        return $summe;
    }

    public function getTageWithBlocks()
    {

        $blocks2 = array();

        $blocks = $this->zeitblocks->toArray();
        $blocks = array_merge($blocks, $this->beworben->toArray());

        foreach ($blocks as $data) {
            if ($data->getGanztag() != 0) {
                $blocks2[$data->getWochentag()][] = $data;
            }
        }
        return sizeof($blocks2);
    }


    /**
     * @return Zeitblock[]
     */
    public function getBetreungsblocksReal()
    {
        $blocks = $this->zeitblocks;
        $realBlocks = array();
        foreach ($blocks as $data) {
            if ($data->getGanztag() != 0) {
                $realBlocks[] = $data;
            }
        }
        return $realBlocks;
    }

    public function getBetreungsblocksRealKontingent()
    {
        $blocks = $this->beworben;
        $realBlocks = array();
        foreach ($blocks as $data) {
            if ($data->getGanztag() != 0) {
                $realBlocks[] = $data;
            }
        }
        return $realBlocks;
    }

    public function getMittagessenblocksReal()
    {
        $blocks = $this->zeitblocks;
        $realBlocks = array();
        foreach ($blocks as $data) {
            if ($data->getGanztag() == 0) {
                $realBlocks[] = $data;
            }
        }
        return $realBlocks;
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

    public function getAllBlocks()
    {
        return array_merge($this->zeitblocks->toArray(), $this->beworben->toArray());
    }

    public function getSaved(): ?bool
    {
        return $this->saved;
    }

    public function setSaved(bool $saved): self
    {
        $this->saved = $saved;

        return $this;
    }

    public function getHistory(): ?int
    {
        return $this->history;
    }

    public function setHistory(int $history): self
    {
        $this->history = $history;

        return $this;
    }

    public function getTracing(): ?string
    {
        return $this->tracing;
    }

    public function setTracing(?string $tracing): self
    {
        $this->tracing = $tracing;

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
            $rechnungen->addKinder($this);
        }

        return $this;
    }

    public function removeRechnungen(Rechnung $rechnungen): self
    {
        if ($this->rechnungen->contains($rechnungen)) {
            $this->rechnungen->removeElement($rechnungen);
            $rechnungen->removeKinder($this);
        }

        return $this;
    }

    public function getArtString()
    {
        $type = array(
            1 => 'Ganztagsbetreuung',
            2 => 'Halbtagsbetreuung',
        );
        return $type[$this->art];
    }

    /**
     * @return Collection|KindFerienblock[]
     */
    public function getKindFerienblocks(): Collection
    {
        return $this->kindFerienblocks;
    }

    /**
     * @return Collection|Ferienblock[]
     */
    public function getFerienblocks(): Collection
    {
        $ferien = array();
        foreach ($this->kindFerienblocks as $data) {
            $ferien[] = $data->getFerienblock();
        }
        return new ArrayCollection($ferien);
    }

    /**
     * @return Collection|KindFerienblock[]
     */
    public function getKindFerienBlock(Ferienblock $ferienblock): ?KindFerienblock
    {
        foreach ($this->kindFerienblocks as $data) {
            if ($data->getFerienblock() == $ferienblock) {
                return $data;
            }
        }
        return null;
    }

    /**
     * @return Collection|KindFerienblock[]
     */
    public function getKindFerienblocksBeworben(): Collection
    {
        $res = array();
        $ferienblock = $this->kindFerienblocks->toArray();
        foreach ($ferienblock as $data) {
            if ($data->getState() == 0) {
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
        foreach ($ferienblock as $data) {
            if ($data->getState() == 10) {
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
        foreach ($ferienblock as $data) {
            if ($data->getState() == 20) {
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
        foreach ($ferienblock as $data) {
            if ($data->getBezahlt() === true) {
                $res[] = $data;
            }
        }
        return new ArrayCollection($res);
    }

    /**
     * @return Integer|Preis
     */
    public function getFerienblockPreis(): float
    {
        $preis = 0.0;
        $ferienblock = $this->kindFerienblocks->toArray();
        foreach ($ferienblock as $data) {
            $preis += $data->getPreis();
        }
        return $preis;
    }

    /**
     * @return Collection|KindFerienblock[]
     */
    public function getKindFerienblocksNichtBezahlt(): Collection
    {
        $res = array();
        $ferienblock = $this->kindFerienblocks->toArray();
        foreach ($ferienblock as $data) {
            if ($data->getBezahlt() === false) {
                $res[] = $data;
            }
        }
        return new ArrayCollection($res);
    }

    public function addKindFerienblock(KindFerienblock $kindFerienblock): self
    {
        if (!$this->kindFerienblocks->contains($kindFerienblock)) {
            $this->kindFerienblocks[] = $kindFerienblock;
            $kindFerienblock->setKind($this);
        }

        return $this;
    }

    public function removeKindFerienblock(KindFerienblock $kindFerienblock): self
    {
        if ($this->kindFerienblocks->contains($kindFerienblock)) {
            $this->kindFerienblocks->removeElement($kindFerienblock);
            // set the owning side to null (unless already changed)
            if ($kindFerienblock->getKind() === $this) {
                $kindFerienblock->setKind(null);
            }
        }

        return $this;
    }

    public function getProgrammFromOrg(Organisation $organisation)
    {
        $res = array();
        foreach ($this->kindFerienblocks as $data) {
            if ($data->getFerienblock()->getOrganisation() == $organisation) {
                $res[] = $data;
            }

        }
        return $res;

    }

    public function getMasernImpfung(): ?bool
    {
        return $this->masernImpfung;
    }

    public function setMasernImpfung(?bool $masernImpfung): self
    {
        $this->masernImpfung = $masernImpfung;

        return $this;
    }

    /**
     * @return Collection|Anwesenheit[]
     */
    public function getAnwesenheitenSchulkindbetreuung(): Collection
    {
        return $this->anwesenheitenSchulkindbetreuung;
    }

    public function addAnwesenheitenSchulkindbetreuung(Anwesenheit $anwesenheitenSchulkindbetreuung): self
    {
        if (!$this->anwesenheitenSchulkindbetreuung->contains($anwesenheitenSchulkindbetreuung)) {
            $this->anwesenheitenSchulkindbetreuung[] = $anwesenheitenSchulkindbetreuung;
            $anwesenheitenSchulkindbetreuung->setKind($this);
        }

        return $this;
    }

    public function removeAnwesenheitenSchulkindbetreuung(Anwesenheit $anwesenheitenSchulkindbetreuung): self
    {
        if ($this->anwesenheitenSchulkindbetreuung->contains($anwesenheitenSchulkindbetreuung)) {
            $this->anwesenheitenSchulkindbetreuung->removeElement($anwesenheitenSchulkindbetreuung);
            // set the owning side to null (unless already changed)
            if ($anwesenheitenSchulkindbetreuung->getKind() === $this) {
                $anwesenheitenSchulkindbetreuung->setKind(null);
            }
        }

        return $this;
    }

    public function getInternalNotice(): ?string
    {
        return $this->internalNotice;
    }

    public function setInternalNotice(?string $internalNotice): self
    {
        $this->internalNotice = $internalNotice;

        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(?\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getZeckenEntfernen(): ?bool
    {
        return $this->zeckenEntfernen;
    }

    public function setZeckenEntfernen(?bool $zeckenEntfernen): self
    {
        $this->zeckenEntfernen = $zeckenEntfernen;

        return $this;
    }


}
