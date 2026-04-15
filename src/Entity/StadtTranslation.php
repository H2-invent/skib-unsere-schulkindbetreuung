<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\TranslationInterface;
use Knp\DoctrineBehaviors\Model\Translatable\TranslationTrait;

#[ORM\Entity]
class StadtTranslation implements TranslationInterface
{
    use TranslationTrait;

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;

    #[ORM\Column(type: 'text', nullable: true)]
    private $infoText;

    #[ORM\Column(type: 'text', nullable: true)]
    private $agb;

    #[ORM\Column(type: 'text', nullable: true)]
    private $datenschutz;

    #[ORM\Column(type: 'text', nullable: true)]
    private $catererInfo;

    #[ORM\Column(type: 'text', nullable: true)]
    private $careBlockInfo;
    #[ORM\Column(type: 'text', nullable: true)]
    private $coverText;

    #[ORM\Column(type: 'text', nullable: true)]
    private $settingGehaltsklassenHelp;

    #[ORM\Column(type: 'text', nullable: true)]
    private $settingKinderimKigaHelp;

    #[ORM\Column(type: 'text', nullable: true)]
    private $settingsAnzahlKindergeldempfangerHelp;

    #[ORM\Column(type: 'text', nullable: true)]
    private $settingsChronicalDesesHelp;
    #[ORM\Column(type: 'text', nullable: true)]
    private $settingsSozielHilfeEmpfangerHelp;
    #[ORM\Column(type: 'text', nullable: true)]
    private $settingsEingabeDerGeschwisterHelp;
    #[ORM\Column(type: 'text', nullable: true)]
    private $settingsweiterePersonenberechtigteHelp;
    #[ORM\Column(type: 'text', nullable: true)]
    private $settingsEingabeDerGeschwisterHelpUpload;

    #[ORM\Column(type: 'text', nullable: true)]
    private $schulindbetreuungPreiseFreitext;

    #[ORM\Column(type: 'text', nullable: true)]
    private $schulkindbetreuungBlockDeaktiviertText;

    #[ORM\Column(type: 'text', nullable: true)]
    private $settings_skib_shoolyear_naming;

    #[ORM\Column(type: 'text', nullable: true)]
    private $settingsSkibTextWhenClosed;

    #[ORM\Column(type: 'text', nullable: true)]
    private $popUpTextVorBezahlung;

    #[ORM\Column(type: 'text', nullable: true)]
    private $settingsSkibPopupRegistrationText;

    #[ORM\Column(type: 'text', nullable: true)]
    private $settingsExtraTextEmailAnmeldungMitBeworben;

    #[ORM\Column(type: 'text', nullable: true)]
    private $settingsExtraTextEmailAnmeldung;

    #[ORM\Column(type: 'text', nullable: true)]
    private $emailtemplateAnmeldung;
    #[ORM\Column(type: 'text', nullable: true)]
    private $emailtemplateBuchung;
    #[ORM\Column(type: 'text', nullable: true)]
    private $emailtemplateAbmeldung;
    #[ORM\Column(type: 'text', nullable: true)]
    private $emailtemplateStammdatenEdit;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getAgb(): ?string
    {
        return $this->agb;
    }

    public function setAgb(?string $agb): self
    {
        $this->agb = $agb;

        return $this;
    }

    public function getDatenschutz(): ?string
    {
        return $this->datenschutz;
    }

    public function setDatenschutz(?string $datenschutz): self
    {
        $this->datenschutz = $datenschutz;

        return $this;
    }

    public function getCatererInfo(): ?string
    {
        return $this->catererInfo;
    }

    public function setCatererInfo(?string $catererInfo): self
    {
        $this->catererInfo = $catererInfo;

        return $this;
    }

    public function getCareBlockInfo(): ?string
    {
        return $this->careBlockInfo;
    }

    public function setCareBlockInfo(?string $careBlockInfo): self
    {
        $this->careBlockInfo = $careBlockInfo;

        return $this;
    }

    public function getCoverText(): ?string
    {
        return $this->coverText;
    }

    public function setCoverText(?string $coverText): self
    {
        $this->coverText = $coverText;

        return $this;
    }

    public function getSettingGehaltsklassenHelp()
    {
        return $this->settingGehaltsklassenHelp;
    }

    public function setSettingGehaltsklassenHelp(mixed $settingGehaltsklassenHelp): self
    {
        $this->settingGehaltsklassenHelp = $settingGehaltsklassenHelp;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSettingKinderimKigaHelp(): string
    {
        return $this->settingKinderimKigaHelp;
    }

    public function setSettingKinderimKigaHelp(mixed $settingKinderimKigaHelp): self
    {
        $this->settingKinderimKigaHelp = $settingKinderimKigaHelp;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSettingsAnzahlKindergeldempfangerHelp(): string
    {
        return $this->settingsAnzahlKindergeldempfangerHelp;
    }

    public function setSettingsAnzahlKindergeldempfangerHelp(mixed $settingsAnzahlKindergeldempfangerHelp): self
    {
        $this->settingsAnzahlKindergeldempfangerHelp = $settingsAnzahlKindergeldempfangerHelp;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSettingsSozielHilfeEmpfangerHelp(): string
    {
        return $this->settingsSozielHilfeEmpfangerHelp;
    }

    public function setSettingsSozielHilfeEmpfangerHelp(mixed $settingsSozielHilfeEmpfangerHelp): self
    {
        $this->settingsSozielHilfeEmpfangerHelp = $settingsSozielHilfeEmpfangerHelp;

        return $this;
    }

    public function getSettingsEingabeDerGeschwisterHelp()
    {
        return $this->settingsEingabeDerGeschwisterHelp;
    }

    public function setSettingsEingabeDerGeschwisterHelp(mixed $settingsEingabeDerGeschwisterHelp): void
    {
        $this->settingsEingabeDerGeschwisterHelp = $settingsEingabeDerGeschwisterHelp;
    }

    public function getSettingsweiterePersonenberechtigteHelp()
    {
        return $this->settingsweiterePersonenberechtigteHelp;
    }

    public function setSettingsweiterePersonenberechtigteHelp(mixed $settingsweiterePersonenberechtigteHelp): void
    {
        $this->settingsweiterePersonenberechtigteHelp = $settingsweiterePersonenberechtigteHelp;
    }

    public function getSettingsEingabeDerGeschwisterHelpUpload()
    {
        return $this->settingsEingabeDerGeschwisterHelpUpload;
    }

    public function setSettingsEingabeDerGeschwisterHelpUpload(mixed $settingsEingabeDerGeschwisterHelpUpload): void
    {
        $this->settingsEingabeDerGeschwisterHelpUpload = $settingsEingabeDerGeschwisterHelpUpload;
    }

    public function getSchulindbetreuungPreiseFreitext()
    {
        return $this->schulindbetreuungPreiseFreitext;
    }

    public function setSchulindbetreuungPreiseFreitext(mixed $schulindbetreuungPreiseFreitext): void
    {
        $this->schulindbetreuungPreiseFreitext = $schulindbetreuungPreiseFreitext;
    }

    public function getSchulkindbetreuungBlockDeaktiviertText()
    {
        return $this->schulkindbetreuungBlockDeaktiviertText;
    }

    public function setSchulkindbetreuungBlockDeaktiviertText(mixed $schulkindbetreuungBlockDeaktiviertText): void
    {
        $this->schulkindbetreuungBlockDeaktiviertText = $schulkindbetreuungBlockDeaktiviertText;
    }

    public function getSettingsSkibShoolyearNaming(): ?string
    {
        return $this->settings_skib_shoolyear_naming;
    }

    public function getSettingsSkibShoolyearNamingArray(): ?array
    {
        $string = $this->settings_skib_shoolyear_naming;
        if (!$string) {
            return [
                1 => '1.Klasse',
                2 => '2.Klasse',
                3 => '3.Klasse',
                4 => '4.Klasse',
            ];
        }

        return json_decode((string) $this->settings_skib_shoolyear_naming, true);
    }

    public function setSettingsSkibShoolyearNaming(?string $settings_skib_shoolyear_naming): self
    {
        $this->settings_skib_shoolyear_naming = $settings_skib_shoolyear_naming;

        return $this;
    }

    public function getSettingsSkibTextWhenClosed()
    {
        return $this->settingsSkibTextWhenClosed;
    }

    public function setSettingsSkibTextWhenClosed(mixed $settingsSkibTextWhenClosed): void
    {
        $this->settingsSkibTextWhenClosed = $settingsSkibTextWhenClosed;
    }

    public function getPopUpTextVorBezahlung()
    {
        return $this->popUpTextVorBezahlung;
    }

    public function setPopUpTextVorBezahlung(mixed $popUpTextVorBezahlung): void
    {
        $this->popUpTextVorBezahlung = $popUpTextVorBezahlung;
    }

    public function getSettingsSkibPopupRegistrationText(): ?string
    {
        return $this->settingsSkibPopupRegistrationText;
    }

    public function setSettingsSkibPopupRegistrationText(?string $settingsSkibPopupRegistrationText): self
    {
        $this->settingsSkibPopupRegistrationText = $settingsSkibPopupRegistrationText;

        return $this;
    }

    public function getSettingsExtraTextEmailAnmeldungMitBeworben()
    {
        return $this->settingsExtraTextEmailAnmeldungMitBeworben;
    }

    public function setSettingsExtraTextEmailAnmeldungMitBeworben(mixed $settingsExtraTextEmailAnmeldungMitBeworben): void
    {
        $this->settingsExtraTextEmailAnmeldungMitBeworben = $settingsExtraTextEmailAnmeldungMitBeworben;
    }

    public function getSettingsExtraTextEmailAnmeldung()
    {
        return $this->settingsExtraTextEmailAnmeldung;
    }

    public function setSettingsExtraTextEmailAnmeldung(mixed $settingsExtraTextEmailAnmeldung): void
    {
        $this->settingsExtraTextEmailAnmeldung = $settingsExtraTextEmailAnmeldung;
    }

    public function getEmailtemplateAnmeldung()
    {
        return $this->emailtemplateAnmeldung;
    }

    public function setEmailtemplateAnmeldung(mixed $emailtemplateAnmeldung): void
    {
        $this->emailtemplateAnmeldung = $emailtemplateAnmeldung;
    }

    public function getEmailtemplateBuchung()
    {
        return $this->emailtemplateBuchung;
    }

    public function setEmailtemplateBuchung(mixed $emailtemplateBuchung): void
    {
        $this->emailtemplateBuchung = $emailtemplateBuchung;
    }

    public function getEmailtemplateAbmeldung()
    {
        return $this->emailtemplateAbmeldung;
    }

    public function setEmailtemplateAbmeldung(mixed $emailtemplateAbmeldung): void
    {
        $this->emailtemplateAbmeldung = $emailtemplateAbmeldung;
    }

    public function getEmailtemplateStammdatenEdit()
    {
        return $this->emailtemplateStammdatenEdit;
    }

    public function setEmailtemplateStammdatenEdit(mixed $emailtemplateStammdatenEdit): void
    {
        $this->emailtemplateStammdatenEdit = $emailtemplateStammdatenEdit;
    }

    public function getSettingsChronicalDesesHelp()
    {
        return $this->settingsChronicalDesesHelp;
    }

    public function setSettingsChronicalDesesHelp(mixed $settingsChronicalDesesHelp): void
    {
        $this->settingsChronicalDesesHelp = $settingsChronicalDesesHelp;
    }
}
