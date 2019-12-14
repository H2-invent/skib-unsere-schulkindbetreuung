<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PaymentRepository")
 */
class Payment
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="float")
     */
    private $summe;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Organisation", inversedBy="paymentsFerien")
     * @ORM\JoinColumn(nullable=false)
     */
    private $organisation;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Stammdaten", inversedBy="paymentFerien")
     * @ORM\JoinColumn(nullable=false)
     */
    private $stammdaten;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="text")
     */
    private $ipAdresse;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\PaymentSepa", inversedBy="payments",cascade={"persist", "remove"})
     */
    private $sepa;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\PaymentBraintree", inversedBy="payment", cascade={"persist", "remove"})
     */
    private $braintree;

    /**
     * @ORM\Column(type="float")
     */
    private $bezahlt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSumme(): ?float
    {
        return $this->summe;
    }

    public function setSumme(float $summe): self
    {
        $this->summe = $summe;

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

    public function getStammdaten(): ?Stammdaten
    {
        return $this->stammdaten;
    }

    public function setStammdaten(?Stammdaten $stammdaten): self
    {
        $this->stammdaten = $stammdaten;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getIpAdresse(): ?string
    {
        return $this->ipAdresse;
    }

    public function setIpAdresse(string $ipAdresse): self
    {
        $this->ipAdresse = $ipAdresse;

        return $this;
    }

    public function getSepa(): ?PaymentSepa
    {
        return $this->sepa;
    }

    public function setSepa(?PaymentSepa $sepa): self
    {
        $this->sepa = $sepa;

        return $this;
    }

    public function getBraintree(): ?PaymentBraintree
    {
        return $this->braintree;
    }

    public function setBraintree(?PaymentBraintree $braintree): self
    {
        $this->braintree = $braintree;

        return $this;
    }

    public function getBezahlt(): ?float
    {
        return $this->bezahlt;
    }

    public function setBezahlt(float $bezahlt): self
    {
        $this->bezahlt = $bezahlt;

        return $this;
    }

}
