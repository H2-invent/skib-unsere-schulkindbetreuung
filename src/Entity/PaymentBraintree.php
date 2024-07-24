<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Repository\PaymentBraintreeRepository::class)]
class PaymentBraintree
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'text')]
    private $token;

    #[ORM\Column(type: 'text', nullable: true)]
    private $nonce;

    #[ORM\Column(type: 'datetime')]
    private $createdAt;

    #[ORM\Column(type: 'text')]
    private $ipAdresse;

    #[ORM\OneToOne(targetEntity: \App\Entity\Payment::class, mappedBy: 'braintree', cascade: ['persist'])]
    private $payment;

    #[ORM\Column(type: 'json', nullable: true)]
    private $result = [];

    #[ORM\Column(type: 'boolean', nullable: true)]
    private $success;

    #[ORM\Column(type: 'text', nullable: true)]
    private $transactionId;



    public function getId(): ?int
    {
        return $this->id;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function getNonce(): ?string
    {
        return $this->nonce;
    }

    public function setNonce(?string $nonce): self
    {
        $this->nonce = $nonce;

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

    public function getPayment(): ?Payment
    {
        return $this->payment;
    }

    public function setPayment(?Payment $payment): self
    {
        $this->payment = $payment;

        // set (or unset) the owning side of the relation if necessary
        $newBraintree = null === $payment ? null : $this;
        if ($payment->getBraintree() !== $newBraintree) {
            $payment->setBraintree($newBraintree);
        }

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

    public function getSuccess(): ?bool
    {
        return $this->success;
    }

    public function setSuccess(?bool $success): self
    {
        $this->success = $success;

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
