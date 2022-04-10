<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use Knp\DoctrineBehaviors\Contract\Entity\TranslationInterface;
use Knp\DoctrineBehaviors\Model\Translatable\TranslationTrait;

/**
 * @ORM\Entity
 */
class StadtTranslation implements TranslationInterface
{
    use TranslationTrait;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $infoText;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $agb;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $datenschutz;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $catererInfo;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $careBlockInfo;
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $coverText;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getAgb(): ?string
    {
        return $this->agb;
    }

    public function setAgb(?string $agb): self
    {
        $this->agb = $agb;

        return $this;
    }

    public function getDatenschutz(): ?string
    {
        return $this->datenschutz;
    }

    public function setDatenschutz(?string $datenschutz): self
    {
        $this->datenschutz = $datenschutz;

        return $this;
    }

    public function getCatererInfo(): ?string
    {
        return $this->catererInfo;
    }

    public function setCatererInfo(?string $catererInfo): self
    {
        $this->catererInfo = $catererInfo;

        return $this;
    }

    public function getCareBlockInfo(): ?string
    {
        return $this->careBlockInfo;
    }

    public function setCareBlockInfo(?string $careBlockInfo): self
    {
        $this->careBlockInfo = $careBlockInfo;

        return $this;
    }
    public function getCoverText(): ?string
    {
        return $this->coverText;
    }

    public function setCoverText(?string $coverText): self
    {
        $this->coverText = $coverText;

        return $this;
    }

    
}
