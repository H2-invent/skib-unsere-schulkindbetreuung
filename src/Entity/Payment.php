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
     * @ORM\Column(type="boolean")
     */
    private $braintree;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $nonce;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $iban;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $bic;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $bankname;

    /**
     * @ORM\Column(type="boolean")
     */
    private $sepa;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $braintreeOK;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $kontobesitzer;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $braintreeToken;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBraintree(): ?bool
    {
        return $this->braintree;
    }

    public function setBraintree(bool $braintree): self
    {
        $this->braintree = $braintree;

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

    public function getIban(): ?string
    {
        return $this->iban;
    }

    public function setIban(?string $iban): self
    {
        $this->iban = $iban;

        return $this;
    }

    public function getBic(): ?string
    {
        return $this->bic;
    }

    public function setBic(?string $bic): self
    {
        $this->bic = $bic;

        return $this;
    }

    public function getBankname(): ?string
    {
        return $this->bankname;
    }

    public function setBankname(?string $bankname): self
    {
        $this->bankname = $bankname;

        return $this;
    }

    public function getSepa(): ?bool
    {
        return $this->sepa;
    }

    public function setSepa(bool $sepa): self
    {
        $this->sepa = $sepa;

        return $this;
    }

    public function getBraintreeOK(): ?bool
    {
        return $this->braintreeOK;
    }

    public function setBraintreeOK(?bool $braintreeOK): self
    {
        $this->braintreeOK = $braintreeOK;

        return $this;
    }

    public function getKontobesitzer(): ?string
    {
        return $this->kontobesitzer;
    }

    public function setKontobesitzer(?string $kontobesitzer): self
    {
        $this->kontobesitzer = $kontobesitzer;

        return $this;
    }

    public function getBraintreeToken(): ?string
    {
        return $this->braintreeToken;
    }

    public function setBraintreeToken(?string $braintreeToken): self
    {
        $this->braintreeToken = $braintreeToken;

        return $this;
    }
}
