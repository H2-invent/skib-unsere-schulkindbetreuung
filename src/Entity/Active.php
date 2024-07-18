<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;

#[ORM\Entity(repositoryClass: \App\Repository\ActiveRepository::class)]
class Active
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'datetime')]
    private $von;

    #[ORM\Column(type: 'datetime')]
    private $bis;

    #[ORM\JoinColumn(nullable: false)]
    #[ORM\ManyToOne(targetEntity: \App\Entity\Stadt::class, inversedBy: 'actives')]
    private $stadt;

    #[ORM\Column(type: 'datetime')]
    private $anmeldeStart;

    #[ORM\Column(type: 'datetime')]
    private $anmeldeEnde;

    #[ORM\OneToMany(targetEntity: \App\Entity\Zeitblock::class, mappedBy: 'active')]
    private $blocks;

    #[ORM\ManyToMany(targetEntity: News::class, mappedBy: 'schuljahre')]
    private $news;



    public function __construct()
    {
        $this->blocks = new ArrayCollection();
        $this->news = new ArrayCollection();
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


    public function getStadt(): ?Stadt
    {
        return $this->stadt;
    }

    public function setStadt(?Stadt $stadt): self
    {
        $this->stadt = $stadt;

        return $this;
    }

    public function getAnmeldeStart(): ?\DateTimeInterface
    {
        return $this->anmeldeStart;
    }

    public function setAnmeldeStart(\DateTimeInterface $anmeldeStart): self
    {
        $this->anmeldeStart = $anmeldeStart;

        return $this;
    }

    public function getAnmeldeEnde(): ?\DateTimeInterface
    {
        return $this->anmeldeEnde;
    }

    public function setAnmeldeEnde(\DateTimeInterface $anmeldeEnde): self
    {
        $this->anmeldeEnde = $anmeldeEnde;

        return $this;
    }

    public function getBlocks(): PersistentCollection
    {
        return $this->blocks;
    }

    public function addBlocks(Zeitblock $block): self
    {
        if (!$this->blocks->contains($block)) {
            $this->blocks[] = $block;
            $block->setActive($this);
        }

        return $this;
    }

    public function removeBlock(Zeitblock $block): self
    {
        if ($this->blocks->contains($block)) {
            $this->blocks->removeElement($block);
            // set the owning side to null (unless already changed)
            if ($block->getActive() === $this) {
                $block->setActive(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, News>
     */
    public function getNews(): Collection
    {
        return $this->news;
    }

    public function addNews(News $news): self
    {
        if (!$this->news->contains($news)) {
            $this->news[] = $news;
            $news->addSchuljahre($this);
        }

        return $this;
    }

    public function removeNews(News $news): self
    {
        if ($this->news->removeElement($news)) {
            $news->removeSchuljahre($this);
        }

        return $this;
    }


}
