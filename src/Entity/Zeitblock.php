<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\TranslatableInterface;

use Knp\DoctrineBehaviors\Model\Translatable\TranslatableTrait;
use Symfony\Component\Serializer\Annotation\Groups;


#[ORM\Entity(repositoryClass: \App\Repository\ZeitblockRepository::class)]
class Zeitblock implements TranslatableInterface
{
    use TranslatableTrait;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['confirm_child'])]
    private $id;

    #[ORM\Column(type: 'time')]
    #[Groups(['confirm_child'])]
    private $von;

    #[ORM\Column(type: 'time')]
    #[Groups(['confirm_child'])]
    private $bis;

    #[ORM\JoinColumn(nullable: false)]
    #[ORM\ManyToOne(targetEntity: \App\Entity\Schule::class, inversedBy: 'zeitblocks')]
    #[Groups(['confirm_child'])]
    private $schule;

    #[ORM\ManyToMany(targetEntity: \App\Entity\Kind::class, inversedBy: 'zeitblocks')]
    private $kind;

    #[ORM\ManyToOne(targetEntity: \App\Entity\Active::class, inversedBy: 'blocks')]
    #[Groups(['confirm_child'])]
    private $active;

    #[ORM\OneToMany(targetEntity: \App\Entity\Abwesend::class, mappedBy: 'zeitblock')]
    private $abwesenheit;

    #[ORM\Column(type: 'integer')]
    #[Groups(['confirm_child'])]
    private $wochentag;

    #[ORM\Column(type: 'integer')]
    private $ganztag;

    #[ORM\Column(type: 'json')]
    private $preise = [];

    #[ORM\Column(type: 'boolean')]
    private $deleted = false;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $min;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $max;

    #[ORM\ManyToMany(targetEntity: \App\Entity\Kind::class, mappedBy: 'beworben')]
    private $kinderBeworben;

    #[ORM\ManyToMany(targetEntity: \App\Entity\Rechnung::class, mappedBy: 'zeitblocks')]
    private $rechnungen;

    #[ORM\ManyToMany(targetEntity: \App\Entity\Zeitblock::class, mappedBy: 'vorganger')]
    private $nachfolger;

    #[ORM\ManyToMany(targetEntity: \App\Entity\Zeitblock::class, inversedBy: 'nachfolger')]
    private $vorganger;

    #[ORM\ManyToMany(targetEntity: self::class, inversedBy: 'nachfolgerSilent')]
    #[ORM\JoinTable(name: 'zeitblock_vorganger_silent')]
    private Collection $vorgangerSilent;

    #[ORM\ManyToMany(targetEntity: self::class, mappedBy: 'vorgangerSilent')]
    #[ORM\JoinTable(name: 'zeitblock_vorganger_silent')]
    private Collection $nachfolgerSilent;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private $deaktiviert;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private $direktbuchungDeaktiviert;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private $hidePrice;

    #[ORM\ManyToOne(targetEntity: Zeitblock::class, inversedBy: 'parentOf')]
    private $cloneOf;

    #[ORM\OneToMany(targetEntity: Zeitblock::class, mappedBy: 'cloneOf')]
    private $parentOf;

    #[ORM\ManyToMany(targetEntity: Kind::class, mappedBy: 'warteliste')]
    private Collection $wartelisteKinder;

    #[ORM\ManyToMany(targetEntity: Kind::class, mappedBy: 'movedToWaiting')]
    private Collection $movedToWaitingKid;

    #[ORM\OneToOne(mappedBy: 'zeitblock', targetEntity: AutoBlockAssignmentChildZeitblock::class, fetch: 'EAGER')]
    private ?AutoBlockAssignmentChildZeitblock $autoBlockAssignmentChildZeitblock = null;

    public function __construct()
    {
        $this->kind = new ArrayCollection();
        $this->abwesenheit = new ArrayCollection();
        $this->kinderBeworben = new ArrayCollection();
        $this->rechnungen = new ArrayCollection();
        $this->nachfolger = new ArrayCollection();
        $this->vorganger = new ArrayCollection();
        $this->parentOf = new ArrayCollection();
        $this->wartelisteKinder = new ArrayCollection();
        $this->movedToWaitingKid = new ArrayCollection();
        $this->vorgangerSilent = new ArrayCollection();
        $this->nachfolgerSilent = new ArrayCollection();
    }

    public function __toString(): string
    {
        return (string)$this->id;
    }

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

    public function getSchule(): ?Schule
    {
        return $this->schule;
    }

    public function setSchule(?Schule $schule): self
    {
        $this->schule = $schule;

        return $this;
    }

    /**
     * @return Collection|Kind[]
     */
    public function getKind(): Collection
    {
        return $this->kind;
    }

    public function getKindwithFin()
    {
        $kind= array();
        foreach($this->kind->toArray() as $data) {

            if($data->getFin() === true) {
                $kind[] = $data;
            }
        }

            return $kind;
    }

    public function getBeworbenwithFin()
    {
        $kind= array();
        foreach($this->kinderBeworben->toArray() as $data) {

            if($data->getEltern()->getCreatedAt()) {
                $kind[] = $data;
            }
        }

        return $kind;
    }
    public function addKind(Kind $kind): self
    {
        if (!$this->kind->contains($kind)) {
            $this->kind[] = $kind;
        }

        return $this;
    }

    public function removeKind(Kind $kind): self
    {
        if ($this->kind->contains($kind)) {
            $this->kind->removeElement($kind);
        }

        return $this;
    }

    public function getActive(): ?Active
    {
        return $this->active;
    }

    public function setActive(?Active $active): self
    {
        $this->active = $active;

        return $this;
    }

    /**
     * @return Collection|Abwesend[]
     */
    public function getAbwesenheit(): Collection
    {
        return $this->abwesenheit;
    }

    public function addAbwesenheit(Abwesend $abwesenheit): self
    {
        if (!$this->abwesenheit->contains($abwesenheit)) {
            $this->abwesenheit[] = $abwesenheit;
            $abwesenheit->setZeitblock($this);
        }

        return $this;
    }

    public function removeAbwesenheit(Abwesend $abwesenheit): self
    {
        if ($this->abwesenheit->contains($abwesenheit)) {
            $this->abwesenheit->removeElement($abwesenheit);
            // set the owning side to null (unless already changed)
            if ($abwesenheit->getZeitblock() === $this) {
                $abwesenheit->setZeitblock(null);
            }
        }

        return $this;
    }

    public function getWochentag(): ?int
    {
        return $this->wochentag;
    }

    public function setWochentag(int $wochentag): self
    {
        $this->wochentag = $wochentag;

        return $this;
    }
    public function getWochentagString(){

       switch ($this->wochentag){
           case 0:
               return "Montag";
               break;
           case 1:
               return "Dienstag";
               break;
           case 2:
               return "Mittwoch";
               break;
           case 3:
               return "Donnerstag";
               break;
           case 4:
               return "Freitag";
               break;
           case 5:
               return "Samstag";
               break;
           case 6:
               return "Sonntag";
               break;
           default:
               return "keine Angabe";
               break;

       }
    }
    public function getWochentagStringShort(){

        switch ($this->wochentag){
            case 0:
                return "Mo";
                break;
            case 1:
                return "Di";
                break;
            case 2:
                return "Mi";
                break;
            case 3:
                return "Do";
                break;
            case 4:
                return "Fr";
                break;
            case 5:
                return "Sa";
                break;
            case 6:
                return "So";
                break;
            default:
                return "keine Angabe";
                break;

        }
    }

    public function getGanztag(): ?int
    {
        return $this->ganztag;
    }

    public function setGanztag(int $ganztag): self
    {
        $this->ganztag = $ganztag;

        return $this;
    }

    public function getPreise(): ?array
    {
        return $this->preise;
    }

    public function setPreise(array $preise): self
    {
        $this->preise = $preise;

        return $this;
    }

    public function getGanztagString(){
        switch ($this->ganztag){
            case 0:
                return 'Mittagessen';
                break;
            case 1:
                return 'Ganztagsbetreuung';
                break;
            case 2:
                return 'Halbtagsbetreuung';
                break;
            default:
                return "keine Angabe";
                break;
        }
    }

    public function getDeleted(): ?bool
    {
        return $this->deleted;
    }


    public function setDeleted(bool $deleted): self
    {
        $this->deleted = $deleted;

        return $this;
    }
    public function getFirstDate() : \DateTimeInterface{
        $date = clone ($this->getActive()->getVon());
        switch ($this->wochentag){
            case 0:
                return $date->modify('next mon');
                break;
            case 1:
                 return $date->modify('next tue');
                break;
            case 2:
                return $date->modify('next wed');
                break;
            case 3:
                return $date->modify('next thu');
                break;
            case 4:
                return $date->modify('next fri');
                break;
            case 5:
                return $date->modify('next sat');
                break;
            case 6:
                return $date->modify('next sun');
                break;
        }
        return $date;
    }

    public function getMin(): ?int
    {
        return $this->min;
    }

    public function setMin(?int $min): self
    {
        $this->min = $min;

        return $this;
    }

    public function getMax(): ?int
    {
        return $this->max;
    }

    public function setMax(?int $max): self
    {
        $this->max = $max;

        return $this;
    }

    /**
     * @return Collection|Kind[]
     */
    public function getKinderBeworben(): Collection
    {
        return $this->kinderBeworben;
    }

    public function addKinderBeworben(Kind $kinderBeworben): self
    {
        if (!$this->kinderBeworben->contains($kinderBeworben)) {
            $this->kinderBeworben[] = $kinderBeworben;
            $kinderBeworben->addBeworben($this);
        }

        return $this;
    }

    public function removeKinderBeworben(Kind $kinderBeworben): self
    {
        if ($this->kinderBeworben->contains($kinderBeworben)) {
            $this->kinderBeworben->removeElement($kinderBeworben);
            $kinderBeworben->removeBeworben($this);
        }

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
            $rechnungen->addZeitblock($this);
        }

        return $this;
    }

    public function removeRechnungen(Rechnung $rechnungen): self
    {
        if ($this->rechnungen->contains($rechnungen)) {
            $this->rechnungen->removeElement($rechnungen);
            $rechnungen->removeZeitblock($this);
        }

        return $this;
    }

    /**
     * @return Collection|Zeitblock[]
     */
    public function getNachfolger(): Collection
    {
        return $this->nachfolger;
    }

    public function addNachfolger(Zeitblock $nachfolger): self
    {
        if (!$this->nachfolger->contains($nachfolger)) {
            $this->nachfolger[] = $nachfolger;
            $nachfolger->addVorganger($this);
        }

        return $this;
    }

    public function removeNachfolger(Zeitblock $nachfolger): self
    {
        if ($this->nachfolger->contains($nachfolger)) {
            $this->nachfolger->removeElement($nachfolger);
            $nachfolger->removeVorganger($this);
        }

        return $this;
    }
    /**
     * @return Collection|Zeitblock[]
     */
    public function getVorganger(): Collection
    {
        return $this->vorganger;
    }

    public function addVorganger(Zeitblock $vorganger): self
    {
        if (!$this->vorganger->contains($vorganger)) {
            $this->vorganger[] = $vorganger;
            $vorganger->addNachfolger($this);
        }

        return $this;
    }

    public function removeVorganger(Zeitblock $vorganger): self
    {
        if ($this->vorganger->contains($vorganger)) {
            $this->vorganger->removeElement($vorganger);
            $vorganger->removeNachfolger($this);
        }

        return $this;
    }

    public function getDeaktiviert(): ?bool
    {
        return $this->deaktiviert;
    }

    public function setDeaktiviert(?bool $deaktiviert): self
    {
        $this->deaktiviert = $deaktiviert;

        return $this;
    }

    public function getDirektbuchungDeaktiviert(): ?bool
    {
        return $this->direktbuchungDeaktiviert;
    }

    public function setDirektbuchungDeaktiviert(?bool $direktbuchungDeaktiviert): self
    {
        $this->direktbuchungDeaktiviert = $direktbuchungDeaktiviert;

        return $this;
    }

    public function getHidePrice(): ?bool
    {
        return $this->hidePrice;
    }

    public function setHidePrice(bool $hidePrice): self
    {
        $this->hidePrice = $hidePrice;

        return $this;
    }

    public function getCloneOf(): ?self
    {
        return $this->cloneOf;
    }

    public function setCloneOf(?self $cloneOf): self
    {
        $this->cloneOf = $cloneOf;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getParentOf(): Collection
    {
        return $this->parentOf;
    }

    public function addParentOf(self $parentOf): self
    {
        if (!$this->parentOf->contains($parentOf)) {
            $this->parentOf[] = $parentOf;
            $parentOf->setCloneOf($this);
        }

        return $this;
    }

    public function removeParentOf(self $parentOf): self
    {
        if ($this->parentOf->removeElement($parentOf)) {
            // set the owning side to null (unless already changed)
            if ($parentOf->getCloneOf() === $this) {
                $parentOf->setCloneOf(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Kind>
     */
    public function getWartelisteKinder(): Collection
    {
        return $this->wartelisteKinder;
    }

    public function addWartelisteKinder(Kind $wartelisteKinder): self
    {
        if (!$this->wartelisteKinder->contains($wartelisteKinder)) {
            $this->wartelisteKinder->add($wartelisteKinder);
            $wartelisteKinder->addWarteliste($this);
        }

        return $this;
    }

    public function removeWartelisteKinder(Kind $wartelisteKinder): self
    {
        if ($this->wartelisteKinder->removeElement($wartelisteKinder)) {
            $wartelisteKinder->removeWarteliste($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Kind>
     */
    public function getMovedToWaitingKid(): Collection
    {
        return $this->movedToWaitingKid;
    }

    public function addMovedToWaitingKid(Kind $movedToWaitingKid): self
    {
        if (!$this->movedToWaitingKid->contains($movedToWaitingKid)) {
            $this->movedToWaitingKid->add($movedToWaitingKid);
            $movedToWaitingKid->addMovedToWaiting($this);
        }

        return $this;
    }

    public function removeMovedToWaitingKid(Kind $movedToWaitingKid): self
    {
        if ($this->movedToWaitingKid->removeElement($movedToWaitingKid)) {
            $movedToWaitingKid->removeMovedToWaiting($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getVorgangerSilent(): Collection
    {
        return $this->vorgangerSilent;
    }

    public function addVorgangerSilent(self $vorgangerSilent): self
    {
        if (!$this->vorgangerSilent->contains($vorgangerSilent)) {
            $this->vorgangerSilent->add($vorgangerSilent);
        }

        return $this;
    }

    public function removeVorgangerSilent(self $vorgangerSilent): self
    {
        $this->vorgangerSilent->removeElement($vorgangerSilent);

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getNachfolgerSilent(): Collection
    {
        return $this->nachfolgerSilent;
    }

    public function addNachfolgerSilent(self $nachfolgerSilent): self
    {
        if (!$this->nachfolgerSilent->contains($nachfolgerSilent)) {
            $this->nachfolgerSilent->add($nachfolgerSilent);
            $nachfolgerSilent->addVorgangerSilent($this);
        }

        return $this;
    }

    public function removeNachfolgerSilent(self $nachfolgerSilent): self
    {
        if ($this->nachfolgerSilent->removeElement($nachfolgerSilent)) {
            $nachfolgerSilent->removeVorgangerSilent($this);
        }

        return $this;
    }

    public function getAutoBlockAssignmentChildZeitblock(): ?AutoBlockAssignmentChildZeitblock
    {
        return $this->autoBlockAssignmentChildZeitblock;
    }

    public function setAutoBlockAssignmentChildZeitblock(AutoBlockAssignmentChildZeitblock $autoBlockAssignmentChildZeitblock): self
    {
        // set the owning side of the relation if necessary
        if ($autoBlockAssignmentChildZeitblock->getZeitblock() !== $this) {
            $autoBlockAssignmentChildZeitblock->setZeitblock($this);
        }

        $this->autoBlockAssignmentChildZeitblock = $autoBlockAssignmentChildZeitblock;

        return $this;
    }
}
