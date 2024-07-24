<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\TranslationInterface;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use Knp\DoctrineBehaviors\Model\Translatable\TranslationTrait;

#[ORM\Entity]
class OrganisationTranslation implements TranslationInterface
{
    use TranslationTrait;

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;

    #[ORM\Column(type: 'text', nullable: true)]
    private $datenschutz;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
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

}
