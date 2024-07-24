<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Repository\PaymentStripeRepository::class)]
class PaymentStripe
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'text')]
    private $chargeId;

    #[ORM\Column(type: 'boolean')]
    private $status;

    #[ORM\OneToOne(targetEntity: \App\Entity\Payment::class, mappedBy: 'paymentStripes', cascade: ['persist'])]
    private $payment;

    #[ORM\Column(type: 'json', nullable: true)]
    private $result = [];

    #[ORM\Column(type: 'text', nullable: true)]
    private $transactionId;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getChargeId(): ?string
    {
        return $this->chargeId;
    }

    public function setChargeId(string $chargeId): self
    {
        $this->chargeId = $chargeId;

        return $this;
    }

    public function getStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): self
    {
        $this->status = $status;

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

    public function getResult(): ?array
    {
        return $this->result;
    }

    public function setResult(?array $result): self
    {
        $this->result = $result;

        return $this;
    }

    public function getTransactionId(): ?string
    {
        return $this->transactionId;
    }

    public function setTransactionId(?string $transactionId): self
    {
        $this->transactionId = $transactionId;

        return $this;
    }
}
