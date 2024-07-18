<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Repository\PaymentRefundRepository::class)]
class PaymentRefund
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'datetime')]
    private $createdAt;

    #[ORM\Column(type: 'float')]
    private $summe;

    #[ORM\Column(type: 'text')]
    private $ipAdresse;

    #[ORM\Column(type: 'integer')]
    private $refundType;

    #[ORM\JoinColumn(nullable: false)]
    #[ORM\ManyToOne(targetEntity: \App\Entity\Payment::class, inversedBy: 'refunds')]
    private $payment;

    #[ORM\Column(type: 'float')]
    private $refundFee;

    #[ORM\Column(type: 'boolean')]
    private $gezahlt;

    #[ORM\Column(type: 'float', nullable: true)]
    private $summeGezahlt;

    #[ORM\Column(type: 'text', nullable: true)]
    private $errorMessage;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getSumme(): ?float
    {
        return $this->summe;
    }

    public function setSumme(float $summe): self
    {
        $this->summe = $summe;

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

    public function getRefundType(): ?int
    {
        return $this->refundType;
    }

    public function setRefundType(int $refundType): self
    {
        $this->refundType = $refundType;

        return $this;
    }

    public function getPayment(): ?Payment
    {
        return $this->payment;
    }

    public function setPayment(?Payment $payment): self
    {
        $this->payment = $payment;

        return $this;
    }

    public function getRefundFee(): ?float
    {
        return $this->refundFee;
    }

    public function setRefundFee(float $refundFee): self
    {
        $this->refundFee = $refundFee;

        return $this;
    }

    public function getGezahlt(): ?bool
    {
        return $this->gezahlt;
    }

    public function setGezahlt(bool $gezahlt): self
    {
        $this->gezahlt = $gezahlt;

        return $this;
    }

    public function getSummeGezahlt(): ?float
    {
        return $this->summeGezahlt;
    }

    public function setSummeGezahlt(?float $summeGezahlt): self
    {
        $this->summeGezahlt = $summeGezahlt;

        return $this;
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function setErrorMessage(?string $errorMessage): self
    {
        $this->errorMessage = $errorMessage;

        return $this;
    }
    public function getTypeAsString(): ?string
    {
        switch ($this->refundType){
            case 0:
                return 'Manuell';
                break;
            case 1:
                return 'Automatisch';
                break;
            default:
                return 'nicht angegebene';
                break;
        }


    }
}
