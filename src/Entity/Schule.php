<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SchuleRepository")
 * @Vich\Uploadable
 */
class Schule
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Assert\NotBlank()
     * @ORM\Column(type="text")
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Organisation", inversedBy="schule")
     * @ORM\JoinColumn(nullable=true)
     */
    private $organisation;



    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Stadt", inversedBy="schules")
     */
    private $stadt;

    /**
     * @Assert\NotBlank()
     * @ORM\Column(type="text")
     */
    private $adresse;

    /**
     * @ORM\Column(type="text", nullable=true)
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
     * @ORM\Column(type="boolean")
     */
    private $deleted = false;
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
     * @ORM\Column(type="datetime",nullable=true)
     * @var \DateTime
     */
    private $updatedAt;

    /**
     * @Assert\NotBlank()
     * @ORM\Column(type="text")
     */
    private $infoText;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Zeitblock", mappedBy="schule", orphanRemoval=true)
     */
    private $zeitblocks;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Kind", mappedBy="schule")
     */
    private $kinder;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Assert\Url()
     */
    private $catererUrl;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $catererName;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Assert\Email()
     */
    private $catererEmail;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\News", mappedBy="schule")
     */
    private $news;

    /**
     * @ORM\ManyToMany(targetEntity=User::class, mappedBy="schulen")
     */
    private $users;


    public function __construct()
    {
        $this->zeitblocks = new ArrayCollection();
        $this->actives = new ArrayCollection();
        $this->kinder = new ArrayCollection();
        $this->news = new ArrayCollection();
        $this->users = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

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
            $zeitblock->setSchule($this);
        }

        return $this;
    }

    public function removeZeitblock(Zeitblock $zeitblock): self
    {
        if ($this->zeitblocks->contains($zeitblock)) {
            $this->zeitblocks->removeElement($zeitblock);
            // set the owning side to null (unless already changed)
            if ($zeitblock->getSchule() === $this) {
                $zeitblock->setSchule(null);
            }
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

    public function getInfoText(): ?string
    {
        return $this->infoText;
    }

    public function setInfoText(?string $infoText): self
    {
        $this->infoText = $infoText;

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
            $kinder->setSchule($this);
        }

        return $this;
    }

    public function removeKinder(Kind $kinder): self
    {
        if ($this->kinder->contains($kinder)) {
            $this->kinder->removeElement($kinder);
            // set the owning side to null (unless already changed)
            if ($kinder->getSchule() === $this) {
                $kinder->setSchule(null);
            }
        }

        return $this;
    }

    public function getCatererUrl(): ?string
    {
        return $this->catererUrl;
    }

    public function setCatererUrl(?string $catererUrl): self
    {
        $this->catererUrl = $catererUrl;

        return $this;
    }

    public function getCatererName(): ?string
    {
        return $this->catererName;
    }

    public function setCatererName(?string $catererName): self
    {
        $this->catererName = $catererName;

        return $this;
    }

    public function getCatererEmail(): ?string
    {
        return $this->catererEmail;
    }

    public function setCatererEmail(?string $catererEmail): self
    {
        $this->catererEmail = $catererEmail;

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
            $news->addSchule($this);
        }

        return $this;
    }

    public function removeNews(News $news): self
    {
        if ($this->news->contains($news)) {
            $this->news->removeElement($news);
            $news->removeSchule($this);
        }

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->addSchulen($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->contains($user)) {
            $this->users->removeElement($user);
            $user->removeSchulen($this);
        }

        return $this;
    }

}
