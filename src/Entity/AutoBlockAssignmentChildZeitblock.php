<?php

namespace App\Entity;

use App\Repository\AutoBlockAssignmentChildZeitblockRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: AutoBlockAssignmentChildZeitblockRepository::class)]
class AutoBlockAssignmentChildZeitblock
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['confirm_child'])]
    private ?int $id = null;

    #[ORM\ManyToOne(cascade: ['persist'], inversedBy: 'zeitblocks')]
    #[ORM\JoinColumn(nullable: false)]
    private ?AutoBlockAssignmentChild $child = null;

    #[ORM\ManyToOne(cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['confirm_child'])]
    private ?Zeitblock $zeitblock = null;

    #[ORM\Column(options: ['default' => false])]
    #[Groups(['confirm_child'])]
    private ?bool $accepted = null;

    #[ORM\Column(options: ['default' => false])]
    #[Groups(['confirm_child'])]
    private ?bool $warteschlange = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getChild(): ?AutoBlockAssignmentChild
    {
        return $this->child;
    }

    public function setChild(?AutoBlockAssignmentChild $child): self
    {
        $this->child = $child;

        return $this;
    }

    public function getZeitblock(): ?Zeitblock
    {
        return $this->zeitblock;
    }

    public function setZeitblock(Zeitblock $zeitblock): self
    {
        $this->zeitblock = $zeitblock;

        return $this;
    }

    public function isAccepted(): ?bool
    {
        return $this->accepted;
    }

    public function setAccepted(bool $accepted): self
    {
        $this->accepted = $accepted;

        return $this;
    }

    public function isWarteschlange(): ?bool
    {
        return $this->warteschlange;
    }

    public function setWarteschlange(bool $warteschlange): self
    {
        $this->warteschlange = $warteschlange;

        return $this;
    }
}
