<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\StammdatenRepository")
 */
class Stammdaten
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
    private $name;

    /**
     * @ORM\Column(type="text")
     */
    private $vorname;

    /**
     * @ORM\Column(type="text")
     */
    private $strasse;

    /**
     * @ORM\Column(type="text",nullable=true)
     */
    private $adresszusatz;

    /**
     * @ORM\Column(type="integer",nullable=true)
     */
    private $einkommen;

    /**
     * @ORM\Column(type="datetime")
     */
    private $created_at;

    /**
     * @ORM\Column(type="boolean")
     */
    private $kinderImKiga = false;

    /**
     * @ORM\Column(type="text")
     */
    private $uid;

    /**
     * @ORM\Column(type="boolean")
     */
    private $angemeldet;

    /**
     * @ORM\Column(type="boolean")
     */
    private $buk;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $beruflicheSituation;

    /**
     * @ORM\Column(type="text")
     */
    private $notfallkontakt;

    /**
     * @ORM\Column(type="boolean")
     */
    private $sepaInfo;

    /**
     * @ORM\Column(type="text")
     */
    private $iban;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Kind", mappedBy="eltern")
     */
    private $kinds;

    /**
     * @ORM\Column(type="text")
     */
    private $bic;

    /**
     * @ORM\Column(type="text")
     */
    private $kontoinhaber;

    /**
     * @ORM\Column(type="boolean")
     */
    private $fin;

    /**
     * @ORM\Column(type="boolean")
     */
    private $gdpr;

    /**
     * @ORM\Column(type="boolean")
     */
    private $newsletter;

    /**
     * @ORM\Column(type="integer")
     */
    private $plz;

    /**
     * @ORM\Column(type="text")
     */
    private $stadt;

    /**
     * @ORM\Column(type="text",nullable=true)
     */
    private $secCode;

    /**
     * @ORM\Column(type="text")
     */
    private $email;

    /**

     * @ORM\Column(type="boolean", nullable=true)
     */
    private $alleinerziehend;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $abholberechtigter;

    /**
     * @ORM\Column(type="text")
     */
    private $notfallName;
    /**
     * @ORM\Column(type="boolean")
     */
    private $emailConfirmed = false;

    /**
     * @ORM\Column(type="text",nullable=true)
     */
    private $confirmationCode;

    /**
     * @ORM\Column(type="boolean")
     */
    private $confirmEmailSend = false;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $resendEmail;


    public function __construct()
    {
        $this->kinds = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getVorname(): ?string
    {
        return $this->vorname;
    }

    public function setVorname(string $vorname): self
    {
        $this->vorname = $vorname;

        return $this;
    }

    public function getStrasse(): ?string
    {
        return $this->strasse;
    }

    public function setStrasse(string $strasse): self
    {
        $this->strasse = $strasse;

        return $this;
    }

    public function getAdresszusatz(): ?string
    {
        return $this->adresszusatz;
    }

    public function setAdresszusatz(string $adresszusatz): self
    {
        $this->adresszusatz = $adresszusatz;

        return $this;
    }

    public function getEinkommen(): ?int
    {
        return $this->einkommen;
    }

    public function setEinkommen(int $einkommen): self
    {
        $this->einkommen = $einkommen;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getKinderImKiga(): ?bool
    {
        return $this->kinderImKiga;
    }

    public function setKinderImKiga(bool $kinderImKiga): self
    {
        $this->kinderImKiga = $kinderImKiga;

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

    public function getAngemeldet(): ?bool
    {
        return $this->angemeldet;
    }

    public function setAngemeldet(bool $angemeldet): self
    {
        $this->angemeldet = $angemeldet;

        return $this;
    }

    public function getBuk(): ?bool
    {
        return $this->buk;
    }

    public function setBuk(bool $buk): self
    {
        $this->buk = $buk;

        return $this;
    }

    public function getBeruflicheSituation(): ?string
    {
        return $this->beruflicheSituation;
    }

    public function setBeruflicheSituation(?string $beruflicheSituation): self
    {
        $this->beruflicheSituation = $beruflicheSituation;

        return $this;
    }

    public function getNotfallkontakt(): ?string
    {
        return $this->notfallkontakt;
    }

    public function setNotfallkontakt(string $notfallkontakt): self
    {
        $this->notfallkontakt = $notfallkontakt;

        return $this;
    }

    public function getSepaInfo(): ?bool
    {
        return $this->sepaInfo;
    }

    public function setSepaInfo(bool $sepaInfo): self
    {
        $this->sepaInfo = $sepaInfo;

        return $this;
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

    /**
     * @return Collection|Kind[]
     */
    public function getKinds(): Collection
    {
        return $this->kinds;
    }

    public function addKind(Kind $kind): self
    {
        if (!$this->kinds->contains($kind)) {
            $this->kinds[] = $kind;
            $kind->setEltern($this);
        }

        return $this;
    }

    public function removeKind(Kind $kind): self
    {
        if ($this->kinds->contains($kind)) {
            $this->kinds->removeElement($kind);
            // set the owning side to null (unless already changed)
            if ($kind->getEltern() === $this) {
                $kind->setEltern(null);
            }
        }

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

    public function getKontoinhaber(): ?string
    {
        return $this->kontoinhaber;
    }

    public function setKontoinhaber(string $kontoinhaber): self
    {
        $this->kontoinhaber = $kontoinhaber;

        return $this;
    }

    public function getFin(): ?bool
    {
        return $this->fin;
    }

    public function setFin(bool $fin): self
    {
        $this->fin = $fin;

        return $this;
    }

    public function getGdpr(): ?bool
    {
        return $this->gdpr;
    }

    public function setGdpr(bool $gdpr): self
    {
        $this->gdpr = $gdpr;

        return $this;
    }

    public function getNewsletter(): ?bool
    {
        return $this->newsletter;
    }

    public function setNewsletter(bool $newsletter): self
    {
        $this->newsletter = $newsletter;

        return $this;
    }

    public function getPlz(): ?int
    {
        return $this->plz;
    }

    public function setPlz(int $plz): self
    {
        $this->plz = $plz;

        return $this;
    }

    public function getStadt(): ?string
    {
        return $this->stadt;
    }

    public function setStadt(string $stadt): self
    {
        $this->stadt = $stadt;

        return $this;
    }

    public function getSecCode(): ?string
    {
        return $this->secCode;
    }

    public function setSecCode(string $secCode): self
    {
        $this->secCode = $secCode;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }


    public function getAlleinerziehend(): ?bool
    {
        return $this->alleinerziehend;
    }

    public function setAlleinerziehend(?bool $alleinerziehend): self
    {
        $this->alleinerziehend = $alleinerziehend;
        return $this;
    }
    public function getEmailConfirmed(): ?bool
    {
        return $this->emailConfirmed;
    }

    public function setEmailConfirmed(bool $emailConfirmed): self
    {
        $this->emailConfirmed = $emailConfirmed;
        return $this;
    }


    public function getAbholberechtigter(): ?string
    {
        return $this->abholberechtigter;
    }

    public function setAbholberechtigter(?string $abholberechtigter): self
    {
        $this->abholberechtigter = $abholberechtigter;
        return  $this;
    }
    public function getConfirmationCode(): ?string
    {
        return $this->confirmationCode;
    }

    public function setConfirmationCode(string $confirmationCode): self
    {
        $this->confirmationCode = $confirmationCode;
        return $this;
    }


    public function getNotfallName(): ?string
    {
        return $this->notfallName;
    }

    public function setNotfallName(string $notfallName): self
    {
        $this->notfallName = $notfallName;
        return $this;
    }

    public function getConfirmEmailSend(): ?bool
    {
        return $this->confirmEmailSend;
    }

    public function setConfirmEmailSend(bool $confirmEmailSend): self
    {
        $this->confirmEmailSend = $confirmEmailSend;

        return $this;
    }

    public function getResendEmail(): ?string
    {
        return $this->resendEmail;
    }

    public function setResendEmail(?string $resendEmail): self
    {
        $this->resendEmail = $resendEmail;
        return $this;
    }
}
