<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\Entity(repositoryClass="App\Repository\NewsRepository")
 * @Vich\Uploadable
 */
class News
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     */
    private $title;

    /**
     * @ORM\Column(type="text")
     */
    private $message;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Stadt", inversedBy="news")
     */
    private $stadt;

    /**
     * @ORM\Column(type="datetime",  nullable=true)
     */
    private $date;

    /**
     * @ORM\Column(type="boolean")
     */
    private $activ;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Organisation", inversedBy="orgNews")
     */
    private $organisation;
    
    /**
     * @ORM\Column(type="datetime")
     */
    private $createdDate;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Schule", inversedBy="news")
     */
    private $schule;

    /**
     * @ORM\ManyToMany(targetEntity=Active::class, inversedBy="news")
     */
    private $schuljahre;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $sendHistory = [];
    /**
     * @ORM\Column(type="string", length=255,nullable=true)
     * @var string
     */
    private $attachment;

    /**
     * @Vich\UploadableField(mapping="data_upload", fileNameProperty="attachment")
     * @var File
     */
    private $attachmentFile;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $sendToAngemeldet;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $sendToGebucht;

    public function __construct()
    {
        $this->schule = new ArrayCollection();
        $this->schuljahre = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;

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

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): ?self
    {
        $this->date = $date;

        return $this;
    }

    public function getActiv(): ?bool
    {
        return $this->activ;
    }

    public function setActiv(bool $activ): self
    {
        $this->activ = $activ;

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


    public function getCreatedDate(): ?\DateTimeInterface
    {
        return $this->createdDate;
    }

    public function setCreatedDate(\DateTimeInterface $createdDate): self
    {
        $this->createdDate = $createdDate;

        return $this;
    }

    /**
     * @return Collection|Schule[]
     */
    public function getSchule(): Collection
    {
        return $this->schule;
    }

    public function addSchule(Schule $schule): self
    {
        if (!$this->schule->contains($schule)) {
            $this->schule[] = $schule;
        }

        return $this;
    }

    public function removeSchule(Schule $schule): self
    {
        if ($this->schule->contains($schule)) {
            $this->schule->removeElement($schule);
        }

        return $this;
    }

    /**
     * @return Collection<int, Active>
     */
    public function getSchuljahre(): Collection
    {
        return $this->schuljahre;
    }

    public function addSchuljahre(Active $schuljahre): self
    {
        if (!$this->schuljahre->contains($schuljahre)) {
            $this->schuljahre[] = $schuljahre;
        }

        return $this;
    }

    public function removeSchuljahre(Active $schuljahre): self
    {
        $this->schuljahre->removeElement($schuljahre);

        return $this;
    }

    public function getSendHistory(): ?array
    {
        return $this->sendHistory;
    }

    public function setSendHistory(?array $sendHistory): self
    {
        $this->sendHistory = $sendHistory;

        return $this;
    }


    public function setAttachmentFile(File $attachmentFile = null)
    {
        $this->attachmentFile = $attachmentFile;

        // VERY IMPORTANT:
        // It is required that at least one field changes if you are using Doctrine,
        // otherwise the event listeners won't be called and the file is lost
        if ($attachmentFile) {
            // if 'updatedAt' is not defined in your entity, use another property
            $this->date = new \DateTime('now');
        }
    }

    public function getAttachmentFile()
    {
        return $this->attachmentFile;
    }

    public function setAttachment($attachment)
    {
        $this->attachment = $this->attachment;
    }

    public function getAttachment()
    {
        return $this->attachment;
    }

    public function getSendToAngemeldet(): ?bool
    {
        return $this->sendToAngemeldet;
    }

    public function setSendToAngemeldet(?bool $sendToAngemeldet): self
    {
        $this->sendToAngemeldet = $sendToAngemeldet;

        return $this;
    }

    public function getSendToGebucht(): ?bool
    {
        return $this->sendToGebucht;
    }

    public function setSendToGebucht(?bool $sendToGebucht): self
    {
        $this->sendToGebucht = $sendToGebucht;

        return $this;
    }

}
