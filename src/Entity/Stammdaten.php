<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\StammdatenRepository")
 */
class Stammdaten
{
    private $beruflicheSituationString;
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Assert\NotBlank()
     * @ORM\Column(type="text", nullable=true)
     */
    private $name;

    /**
     * @Assert\NotBlank()
     * @ORM\Column(type="text",nullable=true)
     */
    private $vorname;

    /**
     * @Assert\NotBlank()
     * @ORM\Column(type="text",nullable=true)
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
    private $buk = false;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $beruflicheSituation;

    /**
     * @Assert\NotBlank()
     * @ORM\Column(type="text",nullable=true)
     */
    private $notfallkontakt;

    /**
     * @Assert\NotBlank()
     * @ORM\Column(type="boolean",nullable=true)
     */
    private $sepaInfo;

    /**
     * @Assert\Iban()
     * @Assert\NotBlank()
     * @ORM\Column(type="text")
     */
    private $iban;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Kind", mappedBy="eltern")
     */
    private $kinds;

    /**
     * @Assert\Bic()
     * @Assert\NotBlank()
     * @ORM\Column(type="text")
     */
    private $bic;

    /**
     * @Assert\NotBlank()
     * @ORM\Column(type="text",nullable=true)
     */
    private $kontoinhaber;

    /**
     * @ORM\Column(type="boolean")
     */
    private $fin;

    /**
     * @Assert\NotBlank()
     * @ORM\Column(type="boolean",nullable=true)
     */
    private $gdpr;

    /**
     * @Assert\NotBlank()
     * @ORM\Column(type="integer",nullable=true)
     */
    private $plz;

    /**
     * @Assert\NotBlank()
     * @ORM\Column(type="text",nullable=true)
     */
    private $stadt;

    /**
     * @ORM\Column(type="text",nullable=true)
     */
    private $secCode;

    /**
     * @Assert\Email()
     * @Assert\NotBlank()
     * @ORM\Column(type="text",nullable=true)
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
     * @Assert\NotBlank()
     * @ORM\Column(type="text", nullable=true)
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

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Rechnung", mappedBy="stammdaten")
     */
    private $rechnungs;

    /**
     * @ORM\Column(type="boolean")
     */
    private $saved = false;

    /**
     * @ORM\Column(type="integer")
     */
    private $history = 0;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $tracing;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $endedAt;


    public function __construct()
    {
        $this->kinds = new ArrayCollection();
        $this->rechnungen = new ArrayCollection();
        $this->rechnungs = new ArrayCollection();

    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getVorname(): ?string
    {
        return $this->vorname;
    }

    public function setVorname(?string $vorname): self
    {
        $this->vorname = $vorname;

        return $this;
    }

    public function getStrasse(): ?string
    {
        return $this->strasse;
    }

    public function setStrasse(?string $strasse): self
    {
        $this->strasse = $strasse;

        return $this;
    }

    public function getAdresszusatz(): ?string
    {
        return $this->adresszusatz;
    }

    public function setAdresszusatz(?string $adresszusatz): self
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
    public function getBeruflicheSituationString(){
        $beruflicheSituationString = array(
            0=>'Keine Angabe',
            1=>'Berufstätig',
            2=>'Arbeitssuchend',
        );
        return $beruflicheSituationString[$this->beruflicheSituation];
    }
    public function getNotfallkontakt(): ?string
    {
        return $this->notfallkontakt;
    }

    public function setNotfallkontakt(?string $notfallkontakt): self
    {
        $this->notfallkontakt = $notfallkontakt;

        return $this;
    }

    public function getSepaInfo(): ?bool
    {
        return $this->sepaInfo;
    }

    public function setSepaInfo(?bool $sepaInfo): self
    {
        $this->sepaInfo = $sepaInfo;

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

    public function setBic(?string $bic): self
    {
        $this->bic = $bic;

        return $this;
    }

    public function getKontoinhaber(): ?string
    {
        return $this->kontoinhaber;
    }

    public function setKontoinhaber(?string $kontoinhaber): self
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

    public function setGdpr(?bool $gdpr): self
    {
        $this->gdpr = $gdpr;

        return $this;
    }

    public function getPlz(): ?int
    {
        return $this->plz;
    }

    public function setPlz(?int $plz): self
    {
        $this->plz = $plz;

        return $this;
    }

    public function getStadt(): ?string
    {
        return $this->stadt;
    }

    public function setStadt(?string $stadt): self
    {
        $this->stadt = $stadt;

        return $this;
    }

    public function getSecCode(): ?string
    {
        return $this->secCode;
    }

    public function setSecCode(?string $secCode): self
    {
        $this->secCode = $secCode;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
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
        return $this;
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

    public function setNotfallName(?string $notfallName): self
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

    /**
     * @return Collection|Rechnung[]
     */
    public function getRechnungs(): Collection
    {
        return $this->rechnungs;
    }

    public function addRechnung(Rechnung $rechnung): self
    {
        if (!$this->rechnungs->contains($rechnung)) {
            $this->rechnungs[] = $rechnung;
            $rechnung->setStammdaten($this);
        }

        return $this;
    }

    public function removeRechnung(Rechnung $rechnung): self
    {
        if ($this->rechnungs->contains($rechnung)) {
            $this->rechnungs->removeElement($rechnung);
            // set the owning side to null (unless already changed)
            if ($rechnung->getStammdaten() === $this) {
                $rechnung->setStammdaten(null);
            }
        }

        return $this;
    }

    public function getSaved(): ?bool
    {
        return $this->saved;
    }

    public function setSaved(bool $saved): self
    {
        $this->saved = $saved;

        return $this;
    }

    public function getHistory(): ?int
    {
        return $this->history;
    }

    public function setHistory(int $history): self
    {
        $this->history = $history;

        return $this;
    }

    public function getTracing(): ?string
    {
        return $this->tracing;
    }

    public function setTracing(?string $tracing): self
    {
        $this->tracing = $tracing;

        return $this;
    }

    public function getEndedAt(): ?\DateTimeInterface
    {
        return $this->endedAt;
    }

    public function setEndedAt(?\DateTimeInterface $endedAt): self
    {
        $this->endedAt = $endedAt;

        return $this;
    }


}
