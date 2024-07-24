<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use Knp\DoctrineBehaviors\Contract\Entity\TranslationInterface;
use Knp\DoctrineBehaviors\Model\Translatable\TranslationTrait;

#[ORM\Entity]
class ZeitblockTranslation implements TranslationInterface
{
    use TranslationTrait;

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;

    #[ORM\Column(type: 'text', nullable: true)]
    private $extraText;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getExtraText(): ?string
    {
        return $this->extraText;
    }

    public function setExtraText(?string $extrText): self
    {
        $this->extraText = $extrText;

        return $this;
    }


}
