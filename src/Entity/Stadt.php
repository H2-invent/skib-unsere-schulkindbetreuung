<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model\Translatable\Translatable as Translatable;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\Entity(repositoryClass="App\Repository\StadtRepository")
 * @Vich\Uploadable
 */
class Stadt
{
    use Translatable;
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Assert\NotBlank()
     * @ORM\Column(type="string", length=32, unique=true,)
     */
    private $slug;

    /**
     * @Assert\NotBlank()
     * @ORM\Column(type="text")
     */
    private $Name;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Anmeldefristen", mappedBy="stadt")
     */
    private $anmeldefristens;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Organisation", mappedBy="stadt")
     */
    private $organisations;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Schule", mappedBy="stadt")
     */
    private $schules;

    /**
     * @ORM\Column(type="datetime")
     */
    private $created_at;

    /**
     * @ORM\Column(type="string", length=255,nullable=true)
     * @var string
     */
    private $image;

    /**
     * @Vich\UploadableField(mapping="profil_picture", fileNameProperty="image")
     * @var File
     */
    private $imageFile;
    /**
     * @ORM\Column(type="string", length=255,nullable=true)
     * @var string
     */
    private $logoStadt;

    /**
     * @Vich\UploadableField(mapping="profil_picture", fileNameProperty="logoStadt")
     * @var File
     */
    private $logoStadtFile;
    /**
     * @ORM\Column(type="datetime",nullable=true)
     * @var \DateTime
     */
    private $updatedAt;

    /**
     * @ORM\Column(type="boolean")
     */
    private $deleted = false;

    /**
     *  @Assert\NotBlank()
     * @ORM\Column(type="text")
     */
    private $email;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $smtpServer;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $smtpPort;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $smtpUsername;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $smtpPassword;

    /**
     * @Assert\NotBlank()
     * @ORM\Column(type="text")
     */
    private $telefon;

    /**
     * @Assert\NotBlank()
     * @ORM\Column(type="text")
     */
    private $adresse;

    /**
     * @ORM\Column(type="text",nullable=true)
     */
    private $adresszusatz;

    /**
     * @Assert\NotBlank()
     * @ORM\Column(type="text")
     */
    private $plz;

    /**
     * @Assert\NotBlank()
     * @ORM\Column(type="text")
     */
    private $ort;

    /**
     * @Assert\NotBlank()
     * @ORM\Column(type="text")
     */
    private $ansprechpartner;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $hauptfarbe;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $akzentfarbe;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $akzentfarbeFehler;



    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Active", mappedBy="stadt")
     */
    private $actives;

    /**
     * @Assert\NotBlank()
     * @ORM\Column(type="integer")
     */
    private $preiskategorien;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $logoUrl;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $stadtHomepage;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\News", mappedBy="stadt")
     */
    private $news;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $berechnungsFormel='
        $adresse = $this->getEltern();
        $summe = 0;
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

        $blocks = $kind->getRealZeitblocks()->toArray();  
        $blocks = array_merge($blocks, $this->beworben->toArray());
        
        $summe += $this->getBetragforKindBetreuung($kind, $adresse);
       ';

    /**
     * @ORM\Column(type="boolean")
     */
    private $ferienprogramm;

    /**
     * @ORM\Column(type="boolean")
     */
    private $schulkindBetreung;


    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Ferienblock", mappedBy="stadt")
     */
    private $ferienblocks;

    /**
     * @ORM\Column(type="json")
     */
    private $gehaltsklassen = [];

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $imprint;

    /**
     * @ORM\Column(type="boolean")
     */
    private $active;

    /**
     * @ORM\Column(type="integer")
     */
    private $minDaysperWeek=1;

    /**
     * @ORM\Column(type="integer")
     */
    private $minBlocksPerDay = 0;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $onlineCheckinEnable;

    /**
     * @ORM\Column(type="boolean")
     */
    private $secCodeAlwaysNew;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $showShowMoreToggleOnHomescreen;



    public function __construct()
    {
        $this->anmeldefristens = new ArrayCollection();
        $this->organisations = new ArrayCollection();
        $this->schules = new ArrayCollection();
        $this->actives = new ArrayCollection();
        $this->news = new ArrayCollection();
        $this->ferienblocks = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->Name;
    }

    public function setName(?string $Name): self
    {
        $this->Name = $Name;

        return $this;
    }

    /**
     * @return Collection|Anmeldefristen[]
     */
    public function getAnmeldefristens(): Collection
    {
        return $this->anmeldefristens;
    }

    public function addAnmeldefristen(Anmeldefristen $anmeldefristen): self
    {
        if (!$this->anmeldefristens->contains($anmeldefristen)) {
            $this->anmeldefristens[] = $anmeldefristen;
            $anmeldefristen->setStadt($this);
        }

        return $this;
    }

    public function removeAnmeldefristen(Anmeldefristen $anmeldefristen): self
    {
        if ($this->anmeldefristens->contains($anmeldefristen)) {
            $this->anmeldefristens->removeElement($anmeldefristen);
            // set the owning side to null (unless already changed)
            if ($anmeldefristen->getStadt() === $this) {
                $anmeldefristen->setStadt(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Organisation[]
     */
    public function getOrganisations(): ?Collection
    {
        return $this->organisations;
    }

    public function addOrganisation(Organisation $organisation): self
    {
        if (!$this->organisations->contains($organisation)) {
            $this->organisations[] = $organisation;
            $organisation->setStadt($this);
        }

        return $this;
    }

    public function removeOrganisation(Organisation $organisation): self
    {
        if ($this->organisations->contains($organisation)) {
            $this->organisations->removeElement($organisation);
            // set the owning side to null (unless already changed)
            if ($organisation->getStadt() === $this) {
                $organisation->setStadt(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Schule[]
     */
    public function getSchules(): Collection
    {
        return $this->schules;
    }

    public function addSchule(Schule $schule): self
    {
        if (!$this->schules->contains($schule)) {
            $this->schules[] = $schule;
            $schule->setStadt($this);
        }

        return $this;
    }

    public function removeSchule(Schule $schule): self
    {
        if ($this->schules->contains($schule)) {
            $this->schules->removeElement($schule);
            // set the owning side to null (unless already changed)
            if ($schule->getStadt() === $this) {
                $schule->setStadt(null);
            }
        }

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
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

    public function setImageFile(File $image = null)
    {
        $this->imageFile = $image;

        // VERY IMPORTANT:
        // It is required that at least one field changes if you are using Doctrine,
        // otherwise the event listeners won't be called and the file is lost
        if ($image) {
            // if 'updatedAt' is not defined in your entity, use another property
            $this->updatedAt = new \DateTime('now');
        }
    }

    public function getImageFile()
    {
        return $this->imageFile;
    }

    public function setImage($image)
    {
        $this->image = $image;
    }

    public function getImage()
    {
        return $this->image;
    }
    public function setLogoStadtFile(File $logoStadtFile = null)
    {
        $this->logoStadtFile = $logoStadtFile;

        // VERY IMPORTANT:
        // It is required that at least one field changes if you are using Doctrine,
        // otherwise the event listeners won't be called and the file is lost
        if ($logoStadtFile) {
            // if 'updatedAt' is not defined in your entity, use another property
            $this->updatedAt = new \DateTime('now');
        }
    }

    public function getLogoStadtFile()
    {
        return $this->logoStadtFile;
    }

    public function setLogoStadt($logoStadt)
    {
        $this->logoStadt = $logoStadt;
    }

    public function getLogoStadt()
    {
        return $this->logoStadt;
    }
    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getSmtpServer(): ?string
    {
        return $this->smtpServer;
    }

    public function setSmtpServer(?string $smtpServer): self
    {
        $this->smtpServer = $smtpServer;

        return $this;
    }

    public function getSmtpPort(): ?string
    {
        return $this->smtpPort;
    }

    public function setSmtpPort(?string $smtpPort): self
    {
        $this->smtpPort = $smtpPort;

        return $this;
    }

    public function getSmtpUsername(): ?string
    {
        return $this->smtpUsername;
    }

    public function setSmtpUsername(?string $smtpUsername): self
    {
        $this->smtpUsername = $smtpUsername;

        return $this;
    }

    public function getSmtpPassword(): ?string
    {
        return $this->smtpPassword;
    }

    public function setSmtpPassword(?string $smtpPassword): self
    {
        $this->smtpPassword = $smtpPassword;

        return $this;
    }

    public function getTelefon(): ?string
    {
        return $this->telefon;
    }

    public function setTelefon(?string $telefon): self
    {
        $this->telefon = $telefon;

        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(?string $adresse): self
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getAdresszusatz(): ?string
    {
        return $this->adresszusatz;
    }

    public function setAdresszusatz(?string $adresszusatz): self
    {
        $this->adresszusatz = $adresszusatz;

        return $this;
    }

    public function getPlz(): ?string
    {
        return $this->plz;
    }

    public function setPlz(?string $plz): self
    {
        $this->plz = $plz;

        return $this;
    }

    public function getOrt(): ?string
    {
        return $this->ort;
    }

    public function setOrt(?string $ort): self
    {
        $this->ort = $ort;

        return $this;
    }

    public function getAnsprechpartner(): ?string
    {
        return $this->ansprechpartner;
    }

    public function setAnsprechpartner(?string $ansprechpartner): self
    {
        $this->ansprechpartner = $ansprechpartner;

        return $this;
    }

    public function getHauptfarbe(): ?string
    {
        return $this->hauptfarbe;
    }

    public function setHauptfarbe(?string $hauptfarbe): self
    {
        $this->hauptfarbe = $hauptfarbe;

        return $this;
    }

    public function getAkzentfarbe(): ?string
    {
        return $this->akzentfarbe;
    }

    public function setAkzentfarbe(?string $akzentfarbe): self
    {
        $this->akzentfarbe = $akzentfarbe;

        return $this;
    }

    public function getAkzentfarbeFehler(): ?string
    {
        return $this->akzentfarbeFehler;
    }

    public function setAkzentfarbeFehler(?string $akzentfarbeFehler): self
    {
        $this->akzentfarbeFehler = $akzentfarbeFehler;

        return $this;
    }



    /**
     * @return Collection|Active[]
     */
    public function getActives(): Collection
    {
        return $this->actives;
    }

    public function addActive(Active $active): self
    {
        if (!$this->actives->contains($active)) {
            $this->actives[] = $active;
            $active->setStadt($this);
        }

        return $this;
    }

    public function removeActive(Active $active): self
    {
        if ($this->actives->contains($active)) {
            $this->actives->removeElement($active);
            // set the owning side to null (unless already changed)
            if ($active->getStadt() === $this) {
                $active->setStadt(null);
            }
        }

        return $this;
    }

    public function getPreiskategorien(): ?int
    {
        return $this->preiskategorien;
    }

    public function setPreiskategorien(?int $preiskategorien): self
    {
        $this->preiskategorien = $preiskategorien;

        return $this;
    }

    public function getLogoUrl(): ?string
    {
        return $this->logoUrl;
    }

    public function setLogoUrl(?string $logoUrl): self
    {
        $this->logoUrl = $logoUrl;

        return $this;
    }

    public function getStadtHomepage(): ?string
    {
        return $this->stadtHomepage;
    }

    public function setStadtHomepage(?string $stadtHomepage): self
    {
        $this->stadtHomepage = $stadtHomepage;

        return $this;
    }

    /**
     * @return Collection|News[]
     */
    public function getNews(): Collection
    {
        return $this->news;
    }

    public function addNews(News $news): self
    {
        if (!$this->news->contains($news)) {
            $this->news[] = $news;
            $news->setStadt($this);
        }

        return $this;
    }

    public function removeNews(News $news): self
    {
        if ($this->news->contains($news)) {
            $this->news->removeElement($news);
            // set the owning side to null (unless already changed)
            if ($news->getStadt() === $this) {
                $news->setStadt(null);
            }
        }

        return $this;
    }

    public function getBerechnungsFormel(): ?string
    {
        return $this->berechnungsFormel;
    }

    public function setBerechnungsFormel(?string $berechnungsFormel): self
    {
        $this->berechnungsFormel = $berechnungsFormel;

        return $this;
    }

    public function getFerienprogramm(): ?bool
    {
        return $this->ferienprogramm;
    }

    public function setFerienprogramm(bool $ferienprogramm): self
    {
        $this->ferienprogramm = $ferienprogramm;

        return $this;
    }

    public function getSchulkindBetreung(): ?bool
    {
        return $this->schulkindBetreung;
    }

    public function setSchulkindBetreung(bool $schulkindBetreung): self
    {
        $this->schulkindBetreung = $schulkindBetreung;

        return $this;
    }





    /**
     * @return Collection|Ferienblock[]
     */
    public function getFerienblocks(): Collection
    {
        return $this->ferienblocks;
    }

    public function addFerienblock(Ferienblock $ferienblock): self
    {
        if (!$this->ferienblocks->contains($ferienblock)) {
            $this->ferienblocks[] = $ferienblock;
            $ferienblock->setStadt($this);
        }

        return $this;
    }

    public function removeFerienblock(Ferienblock $ferienblock): self
    {
        if ($this->ferienblocks->contains($ferienblock)) {
            $this->ferienblocks->removeElement($ferienblock);
            // set the owning side to null (unless already changed)
            if ($ferienblock->getStadt() === $this) {
                $ferienblock->setStadt(null);
            }
        }

        return $this;
    }

    public function getGehaltsklassen(): ?array
    {
        return $this->gehaltsklassen;
    }

    public function setGehaltsklassen(array $gehaltsklassen): self
    {
        $this->gehaltsklassen = $gehaltsklassen;

        return $this;
    }

    public function getImprint(): ?string
    {
        return $this->imprint;
    }

    public function setImprint(?string $imprint): self
    {
        $this->imprint = $imprint;

        return $this;
    }

    public function getActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    public function getMinDaysperWeek(): ?int
    {
        return $this->minDaysperWeek;
    }

    public function setMinDaysperWeek(int $minDaysperWeek): self
    {
        $this->minDaysperWeek = $minDaysperWeek;

        return $this;
    }

    public function getMinBlocksPerDay(): ?int
    {
        return $this->minBlocksPerDay;
    }

    public function setMinBlocksPerDay(int $minBlocksPerDay): self
    {
        $this->minBlocksPerDay = $minBlocksPerDay;

        return $this;
    }

    public function getOnlineCheckinEnable(): ?bool
    {
        return $this->onlineCheckinEnable;
    }

    public function setOnlineCheckinEnable(?bool $onlineCheckinEnable): self
    {
        $this->onlineCheckinEnable = $onlineCheckinEnable;

        return $this;
    }

    public function getSecCodeAlwaysNew(): ?bool
    {
        return $this->secCodeAlwaysNew;
    }

    public function setSecCodeAlwaysNew(bool $secCodeAlwaysNew): self
    {
        $this->secCodeAlwaysNew = $secCodeAlwaysNew;

        return $this;
    }

    public function getShowShowMoreToggleOnHomescreen(): ?bool
    {
        return $this->showShowMoreToggleOnHomescreen;
    }

    public function setShowShowMoreToggleOnHomescreen(?bool $showShowMoreToggleOnHomescreen): self
    {
        $this->showShowMoreToggleOnHomescreen = $showShowMoreToggleOnHomescreen;

        return $this;
    }



}
