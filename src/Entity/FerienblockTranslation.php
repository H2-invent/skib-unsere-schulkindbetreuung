<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\TranslationInterface;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use Knp\DoctrineBehaviors\Model\Translatable\TranslationTrait;

#[ORM\Entity]
class FerienblockTranslation  implements TranslationInterface
{
    use TranslationTrait;
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;
    #[ORM\Column(type: 'text', nullable: true)]
    private $titel;

    #[ORM\Column(type: 'text', nullable: true)]
    private $infoText;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    public function getTitel(): ?string
    {
        return $this->titel;
    }

    public function setTitel(?string $titel): self
    {
        $this->titel = $titel;

        return $this;
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

}
