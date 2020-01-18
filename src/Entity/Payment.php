<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\PaymentRefund", mappedBy="payment")
     */
    private $refunds;

    /**
     * @ORM\Column(type="text")
     */
    private $uid;

    /**
     * @ORM\Column(type="boolean")
     */
    private $finished;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\PaymentStripe", inversedBy="payment",cascade={"persist", "remove"})
     */
    private $paymentStripes;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $artString;

    public function __construct()
    {
        $this->refunds = new ArrayCollection();
    }

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
    public function getTypeAsString(): ?string
    {
        $output =array();
        if($this->sepa){
            $output[] = 'SEPA';
        }
        if ($this->braintree){
            $output[] = 'Braintree';
        }
        if ($this->paymentStripes){
            $output[] = 'Stripe';
        }
        // hier können noch mehr Zahlmetoden rein
        return  implode(', ',$output );
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

    /**
     * @return Collection|PaymentRefund[]
     */
    public function getRefunds(): Collection
    {
        return $this->refunds;
    }

    public function addRefund(PaymentRefund $refund): self
    {
        if (!$this->refunds->contains($refund)) {
            $this->refunds[] = $refund;
            $refund->setPayment($this);
        }

        return $this;
    }

    public function removeRefund(PaymentRefund $refund): self
    {
        if ($this->refunds->contains($refund)) {
            $this->refunds->removeElement($refund);
            // set the owning side to null (unless already changed)
            if ($refund->getPayment() === $this) {
                $refund->setPayment(null);
            }
        }

        return $this;
    }

    public function getUid(): ?string
    {
        return $this->uid;
    }

    public function setUid(string $uid): self
    {
        $this->uid = $uid;

        return $this;
    }

    public function getFinished(): ?bool
    {
        return $this->finished;
    }

    public function setFinished(bool $finished): self
    {
        $this->finished = $finished;

        return $this;
    }

    public function getPaymentStripe(): ?PaymentStripe
    {
        return $this->paymentStripes;
    }

    public function setPaymentStripe(?PaymentStripe $paymentStripe): self
    {
        $this->paymentStripes = $paymentStripe;

        return $this;
    }

    public function getArtString(): ?string
    {
        return $this->artString;
    }

    public function setArtString(?string $artString): self
    {
        $this->artString = $artString;

        return $this;
    }


}
