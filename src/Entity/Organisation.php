<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model\Translatable\Translatable as Translatable;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\Validator\Constraints as Assert;
/**
 * @ORM\Entity(repositoryClass="App\Repository\OrganisationRepository")
 * @Vich\Uploadable
 */
class Organisation
{
    use Translatable;
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Assert\NotBlank()
     * @ORM\Column(type="text")
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Schule", mappedBy="organisation")
     */
    private $schule;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Stadt", inversedBy="organisations")
     */
    private $stadt;

    /**
     * @ORM\Column(type="boolean")
     */
    private $deleted = false;

    /**
     * @Assert\NotBlank()
     * @ORM\Column(type="text")
     */
    private $adresse;

    /**
     * @ORM\Column(type="text",nullable=true)
     */
    private $adresszusatz;

    /**
     * @Assert\NotBlank()
     * @ORM\Column(type="text")
     */
    private $plz;

    /**
     * @Assert\NotBlank()
     * @ORM\Column(type="text")
     */
    private $ort;

    /**
     * @Assert\NotBlank()
     * @ORM\Column(type="text")
     */
    private $ansprechpartner;

    /**
     * @Assert\Iban()
     * @Assert\NotBlank()
     * @ORM\Column(type="text")
     */
    private $iban;

    /**
     * @Assert\Bic()
     * @Assert\NotBlank()
     * @ORM\Column(type="text")
     */
    private $bic;

    /**
     * @Assert\NotBlank()
     * @ORM\Column(type="text")
     */
    private $bankName;

    /**
     * @Assert\NotBlank()
     * @ORM\Column(type="text")
     */
    private $glauaubigerId;

    /**
     * @Assert\NotBlank()
     * @ORM\Column(type="text")
     */
    private $infoText;

    /**
     * @Assert\NotBlank()
     * @ORM\Column(type="text")
     */
    private $telefon;

    /**
     * @Assert\NotBlank()
     * @Assert\Email()
     * @ORM\Column(type="text")
     */
    private $email;

    /**
     * @ORM\Column(type="text",nullable=true)
     */
    private $smptServer;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $smtpPort;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $smtpUser;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $smtpPassword;
    /**
     * @ORM\Column(type="string", length=255,nullable=true)
     * @var string
     */
    private $image;

    /**
     * @Vich\UploadableField(mapping="profil_picture", fileNameProperty="image")
     * @var File
     */
    private $imageFile;

    /**
     * @ORM\Column(type="datetime",nullable=true)
     * @var \DateTime
     */
    private $updatedAt;


    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Sepa", mappedBy="organisation")
     */
    private $sepas;

    /**
     * @Assert\NotBlank()
     * @ORM\Column(type="text")
     */
    private $steuernummer;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     */
    private $umstid;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $orgHomepage;

    /**
     * @ORM\Column(type="boolean")
     */
    private $ferienprogramm;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $paypalId;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $paypalSecret;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $paypalSignature;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $stripeID;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $stripeSecret;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $stornoGebuehr;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $ansprechpartnerFerien;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $ansprechpartnerFerienPhone;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $ansprechpartnerFerienEmail;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Ferienblock", mappedBy="organisation")
     */
    private $ferienblocks;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Payment", mappedBy="organisation")
     */
    private $paymentsFerien;




    public function __construct()
    {
        $this->schule = new ArrayCollection();

        $this->sepas = new ArrayCollection();
        $this->ferienblocks = new ArrayCollection();
        $this->PaymentsFerien = new ArrayCollection();
        $this->paymentsFerien = new ArrayCollection();
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

    /**
     * @return Collection|schule[]
     */
    public function getSchule(): Collection
    {
        $schuleRet = array();
        foreach ($this->schule as $data) {
          if($data->getDeleted() == false){
            $schuleRet[] = $data;
          }
        }
        $this->schule= (new ArrayCollection($schuleRet));
        return $this->schule;
    }

    public function addSchule(Schule $schule): self
    {
        if (!$this->schule->contains($schule)) {
            $this->schule[] = $schule;
            $schule->setOrganisation($this);
        }

        return $this;
    }

    public function removeSchule(Schule $schule): self
    {
        if ($this->schule->contains($schule)) {
            $this->schule->removeElement($schule);
            // set the owning side to null (unless already changed)
            if ($schule->getOrganisation() === $this) {
                $schule->setOrganisation(null);
            }
        }

        return $this;
    }

    public function getStadt(): ?Stadt
    {
        return $this->stadt;
    }

    public function setStadt(?Stadt $stadt): self
    {
        $this->stadt = $stadt;

        return $this;
    }

    public function getDeleted(): ?bool
    {
        return $this->deleted;
    }

    public function setDeleted(bool $deleted): self
    {
        $this->deleted = $deleted;

        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(?string $adresse): self
    {
        $this->adresse = $adresse;

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

    public function getPlz(): ?string
    {
        return $this->plz;
    }

    public function setPlz(?string $plz): self
    {
        $this->plz = $plz;

        return $this;
    }

    public function getOrt(): ?string
    {
        return $this->ort;
    }

    public function setOrt(?string $ort): self
    {
        $this->ort = $ort;

        return $this;
    }

    public function getAnsprechpartner(): ?string
    {
        return $this->ansprechpartner;
    }

    public function setAnsprechpartner(?string $ansprechpartner): self
    {
        $this->ansprechpartner = $ansprechpartner;

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

    public function getBankName(): ?string
    {
        return $this->bankName;
    }

    public function setBankName(?string $bankName): self
    {
        $this->bankName = $bankName;

        return $this;
    }

    public function getGlauaubigerId(): ?string
    {
        return $this->glauaubigerId;
    }

    public function setGlauaubigerId(?string $glauaubigerId): self
    {
        $this->glauaubigerId = $glauaubigerId;

        return $this;
    }

    public function getInfoText(): ?string
    {
        return $this->infoText;
    }

    public function setInfoText(?string $infoText): self
    {
        $this->infoText = $infoText;

        return $this;
    }

    public function getTelefon(): ?string
    {
        return $this->telefon;
    }

    public function setTelefon(?string $telefon): self
    {
        $this->telefon = $telefon;

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

    public function getSmptServer(): ?string
    {
        return $this->smptServer;
    }

    public function setSmptServer(?string $smptServer): self
    {
        $this->smptServer = $smptServer;

        return $this;
    }

    public function getSmtpPort(): ?int
    {
        return $this->smtpPort;
    }

    public function setSmtpPort(?int $smtpPort): self
    {
        $this->smtpPort = $smtpPort;

        return $this;
    }

    public function getSmtpUser(): ?string
    {
        return $this->smtpUser;
    }

    public function setSmtpUser(?string $smtpUser): self
    {
        $this->smtpUser = $smtpUser;

        return $this;
    }

    public function getSmtpPassword(): ?string
    {
        return $this->smtpPassword;
    }

    public function setSmtpPassword(?string $smtpPassword): self
    {
        $this->smtpPassword = $smtpPassword;

        return $this;
    }
    public function setImageFile(File $image = null)
    {
        $this->imageFile = $image;

        // VERY IMPORTANT:
        // It is required that at least one field changes if you are using Doctrine,
        // otherwise the event listeners won't be called and the file is lost
        if ($image) {
            // if 'updatedAt' is not defined in your entity, use another property
            $this->updatedAt = new \DateTime('now');
        }
    }

    public function getImageFile()
    {
        return $this->imageFile;
    }

    public function setImage($image)
    {
        $this->image = $image;
    }

    public function getImage()
    {
        return $this->image;
    }


    /**
     * @return Collection|Sepa[]
     */
    public function getSepas(): Collection
    {
        return $this->sepas;
    }

    public function addSepa(Sepa $sepa): self
    {
        if (!$this->sepas->contains($sepa)) {
            $this->sepas[] = $sepa;
            $sepa->setOrganisation($this);
        }

        return $this;
    }

    public function removeSepa(Sepa $sepa): self
    {
        if ($this->sepas->contains($sepa)) {
            $this->sepas->removeElement($sepa);
            // set the owning side to null (unless already changed)
            if ($sepa->getOrganisation() === $this) {
                $sepa->setOrganisation(null);
            }
        }

        return $this;
    }

    public function getSteuernummer(): ?string
    {
        return $this->steuernummer;
    }

    public function setSteuernummer(string $steuernummer): self
    {
        $this->steuernummer = $steuernummer;

        return $this;
    }

    public function getUmstid(): ?string
    {
        return $this->umstid;
    }

    public function setUmstid(string $umstid): self
    {
        $this->umstid = $umstid;

        return $this;
    }

    public function getOrgHomepage(): ?string
    {
        return $this->orgHomepage;
    }

    public function setOrgHomepage(?string $orgHomepage): self
    {
        $this->orgHomepage = $orgHomepage;

        return $this;
    }

    public function getFerienprogramm(): ?bool
    {
        return $this->ferienprogramm;
    }

    public function setFerienprogramm(bool $ferienprogramm): self
    {
        $this->ferienprogramm = $ferienprogramm;

        return $this;
    }

    public function getPaypalId(): ?string
    {
        return $this->paypalId;
    }

    public function setPaypalId(?string $paypalId): self
    {
        $this->paypalId = $paypalId;

        return $this;
    }

    public function getPaypalSecret(): ?string
    {
        return $this->paypalSecret;
    }

    public function setPaypalSecret(?string $paypalSecret): self
    {
        $this->paypalSecret = $paypalSecret;

        return $this;
    }

    public function getPaypalSignature(): ?string
    {
        return $this->paypalSignature;
    }

    public function setPaypalSignature(?string $paypalSignature): self
    {
        $this->paypalSignature = $paypalSignature;

        return $this;
    }

    public function getStripeID(): ?string
    {
        return $this->stripeID;
    }

    public function setStripeID(?string $stripeID): self
    {
        $this->stripeID = $stripeID;

        return $this;
    }

    public function getStripeSecret(): ?string
    {
        return $this->stripeSecret;
    }

    public function setStripeSecret(?string $stripeSecret): self
    {
        $this->stripeSecret = $stripeSecret;

        return $this;
    }

    public function getStornoGebuehr(): ?float
    {
        return $this->stornoGebuehr;
    }

    public function setStornoGebuehr(?float $stornoGebuehr): self
    {
        $this->stornoGebuehr = $stornoGebuehr;

        return $this;
    }

    public function getAnsprechpartnerFerien(): ?string
    {
        return $this->ansprechpartnerFerien;
    }

    public function setAnsprechpartnerFerien(?string $ansprechpartnerFerien): self
    {
        $this->ansprechpartnerFerien = $ansprechpartnerFerien;

        return $this;
    }

    public function getAnsprechpartnerFerienPhone(): ?string
    {
        return $this->ansprechpartnerFerienPhone;
    }

    public function setAnsprechpartnerFerienPhone(?string $ansprechpartnerFerienPhone): self
    {
        $this->ansprechpartnerFerienPhone = $ansprechpartnerFerienPhone;

        return $this;
    }

    public function getAnsprechpartnerFerienEmail(): ?string
    {
        return $this->ansprechpartnerFerienEmail;
    }

    public function setAnsprechpartnerFerienEmail(?string $ansprechpartnerFerienEmail): self
    {
        $this->ansprechpartnerFerienEmail = $ansprechpartnerFerienEmail;

        return $this;
    }

    /**
     * @return Collection|Ferienblock[]
     */
    public function getFerienblocks(): Collection
    {
        return $this->ferienblocks;
    }

    public function addFerienblock(Ferienblock $ferienblock): self
    {
        if (!$this->ferienblocks->contains($ferienblock)) {
            $this->ferienblocks[] = $ferienblock;
            $ferienblock->setOrganisation($this);
        }

        return $this;
    }

    public function removeFerienblock(Ferienblock $ferienblock): self
    {
        if ($this->ferienblocks->contains($ferienblock)) {
            $this->ferienblocks->removeElement($ferienblock);
            // set the owning side to null (unless already changed)
            if ($ferienblock->getOrganisation() === $this) {
                $ferienblock->setOrganisation(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Payment[]
     */
    public function getPaymentsFerien(): Collection
    {
        return $this->paymentsFerien;
    }

    public function addPaymentsFerien(Payment $paymentsFerien): self
    {
        if (!$this->paymentsFerien->contains($paymentsFerien)) {
            $this->paymentsFerien[] = $paymentsFerien;
            $paymentsFerien->setOrganisation($this);
        }

        return $this;
    }

    public function removePaymentsFerien(Payment $paymentsFerien): self
    {
        if ($this->paymentsFerien->contains($paymentsFerien)) {
            $this->paymentsFerien->removeElement($paymentsFerien);
            // set the owning side to null (unless already changed)
            if ($paymentsFerien->getOrganisation() === $this) {
                $paymentsFerien->setOrganisation(null);
            }
        }

        return $this;
    }




}
