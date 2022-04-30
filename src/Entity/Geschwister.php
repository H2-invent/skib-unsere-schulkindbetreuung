<?php

namespace App\Entity;

use App\Repository\GeschwisterRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=GeschwisterRepository::class)
 */
class Geschwister
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Assert\NotBlank() (groups={"all"})
     */
    private $vorname;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Assert\NotBlank(groups={"all"})
     */
    private $nachname;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Assert\NotBlank(groups={"all"})
     */
    private $geburtsdatum;

    /**
     * @ORM\ManyToOne(targetEntity=Stammdaten::class, inversedBy="geschwisters")
     * @ORM\JoinColumn(nullable=false)
     */
    private $stammdaten;

    /**
     * @ORM\ManyToMany(targetEntity=File::class)
     */
    private $file;

    public function __construct()
    {
        $this->file = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getGeburtsdatum(): ?\DateTimeInterface
    {
        return $this->geburtsdatum;
    }

    public function setGeburtsdatum(?\DateTimeInterface $geburtsdatum): self
    {
        $this->geburtsdatum = $geburtsdatum;

        return $this;
    }

    public function getStammdaten(): ?Stammdaten
    {
        return $this->stammdaten;
    }

    public function setStammdaten(?Stammdaten $stammdaten): self
    {
        $this->stammdaten = $stammdaten;

        return $this;
    }

    /**
     * @return Collection<int, File>
     */
    public function getFile(): Collection
    {
        return $this->file;
    }

    public function addFile(File $file): self
    {
        if (!$this->file->contains($file)) {
            $this->file[] = $file;
        }

        return $this;
    }

    public function removeFile(File $file): self
    {
        $this->file->removeElement($file);

        return $this;
    }
}
