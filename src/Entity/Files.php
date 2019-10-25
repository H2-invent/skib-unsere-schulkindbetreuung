<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\FilesRepository")
 */
class Files
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
    private $filename;

    /**
     * @ORM\Column(type="blob")
     */
    private $fileContent;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Stammdaten", inversedBy="rechnungen")
     */
    private $rechnung;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Organisation", inversedBy="sepaXml")
     */
    private $sepa;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): self
    {
        $this->filename = $filename;

        return $this;
    }

    public function getFileContent()
    {
        return $this->fileContent;
    }

    public function setFileContent($fileContent): self
    {
        $this->fileContent = $fileContent;

        return $this;
    }

    public function getRechnung(): ?stammdaten
    {
        return $this->rechnung;
    }

    public function setRechnung(?stammdaten $rechnung): self
    {
        $this->rechnung = $rechnung;

        return $this;
    }

    public function getSepa(): ?organisation
    {
        return $this->sepa;
    }

    public function setSepa(?organisation $sepa): self
    {
        $this->sepa = $sepa;

        return $this;
    }
}
