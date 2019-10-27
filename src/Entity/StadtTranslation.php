<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;

/**
 * @ORM\Entity
 */
class StadtTranslation
{
    use ORMBehaviors\Translatable\Translation;


    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $infoText;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $agb;



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


    
}
