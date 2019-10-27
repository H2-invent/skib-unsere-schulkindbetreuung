<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model\Translatable\Translatable as Translatable;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\Validator\Constraints as Assert;

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
     * * @Assert\NotBlank()
     * @ORM\Column(type="string", length=32, unique=true,)
     */
    private $slug;

    /**
     * * @Assert\NotBlank()
     * @ORM\Column(type="text")
     */
    private $Name;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Anmeldefristen", mappedBy="stadt")
     */
    private $anmeldefristens;

    /*
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
    private $agb;

    /**
     * @Vich\UploadableField(mapping="data_upload", fileNameProperty="agb")
     * @var File
     */
    private $agbFile;

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


    public function __construct()
    {
        $this->anmeldefristens = new ArrayCollection();
        $this->organisations = new ArrayCollection();
        $this->schules = new ArrayCollection();
        $this->actives = new ArrayCollection();
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
    public function getOrganisations(): Collection
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
    public function setAgbFile(File $agb = null)
    {
        $this->agbFile = $agb;

        // VERY IMPORTANT:
        // It is required that at least one field changes if you are using Doctrine,
        // otherwise the event listeners won't be called and the file is lost
        if ($agb) {
            // if 'updatedAt' is not defined in your entity, use another property
            $this->updatedAt = new \DateTime('now');
        }
    }

    public function getAgbFile()
    {
        return $this->imageFile;
    }

    public function setAgb($image)
    {
        $this->image = $image;
    }

    public function getAgb()
    {
        return $this->image;
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

}
