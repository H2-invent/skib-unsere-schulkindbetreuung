<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
#[ORM\Entity(repositoryClass: \App\Repository\PaymentSepaRepository::class)]
class PaymentSepa
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank]
    #[Assert\Iban]
    private $iban;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank]
    #[Assert\Bic]
    private $bic;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank]
    private $bankName;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank]
    private $kontoinhaber;

    #[ORM\Column(type: 'boolean')]
    #[Assert\NotBlank]
    private $sepaAllowed;

    #[ORM\OneToMany(targetEntity: \App\Entity\Payment::class, mappedBy: 'sepa')]
    private $payments;

    public function __construct()
    {
        $this->payments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIban(): ?string
    {
        return $this->iban;
    }

    public function setIban(string $iban): self
    {
        $this->iban = $iban;

        return $this;
    }

    public function getBic(): ?string
    {
        return $this->bic;
    }

    public function setBic(string $bic): self
    {
        $this->bic = $bic;

        return $this;
    }

    public function getBankName(): ?string
    {
        return $this->bankName;
    }

    public function setBankName(string $bankName): self
    {
        $this->bankName = $bankName;

        return $this;
    }

    public function getKontoinhaber(): ?string
    {
        return $this->kontoinhaber;
    }

    public function setKontoinhaber(string $kontoinhaber): self
    {
        $this->kontoinhaber = $kontoinhaber;

        return $this;
    }

    public function getSepaAllowed(): ?bool
    {
        return $this->sepaAllowed;
    }

    public function setSepaAllowed(bool $sepaAllowed): self
    {
        $this->sepaAllowed = $sepaAllowed;

        return $this;
    }

    /**
     * @return Collection|Payment[]
     */
    public function getPayments(): Collection
    {
        return $this->payments;
    }

    public function addPayment(Payment $payment): self
    {
        if (!$this->payments->contains($payment)) {
            $this->payments[] = $payment;
            $payment->setSepa($this);
        }

        return $this;
    }

    public function removePayment(Payment $payment): self
    {
        if ($this->payments->contains($payment)) {
            $this->payments->removeElement($payment);
            // set the owning side to null (unless already changed)
            if ($payment->getSepa() === $this) {
                $payment->setSepa(null);
            }
        }

        return $this;
    }
}
