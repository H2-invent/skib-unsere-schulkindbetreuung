<?php
/**
 * Created by PhpStorm.
 * User: Emanuel
 * Date: 17.09.2019
 * Time: 20:29
 */

namespace App\Form\Type;


use A2lix\TranslationFormBundle\Form\Type\TranslationsType;
use App\Entity\Stadt;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;

class StadtType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $stadt = $options['data'];
        if ($stadt->getTranslations()->isEmpty()) {
            $stadt->translate('de')->setInfoText('');
            $stadt->translate('en')->setInfoText('');
            $stadt->translate('fr')->setInfoText('');
            $stadt->translate('de')->setAgb('');
            $stadt->translate('en')->setAgb('');
            $stadt->translate('fr')->setAgb('');
            $stadt->translate('de')->setDatenschutz('');
            $stadt->translate('en')->setDatenschutz('');
            $stadt->translate('fr')->setDatenschutz('');
            $stadt->translate('de')->setCatererInfo('');
            $stadt->translate('en')->setCatererInfo('');
            $stadt->translate('fr')->setCatererInfo('');
            $stadt->translate('de')->setCareBlockInfo('');
            $stadt->translate('en')->setCareBlockInfo('');
            $stadt->translate('fr')->setCareBlockInfo('');

            $stadt->translate('de')->setSettingGehaltsklassenHelp('');
            $stadt->translate('en')->setSettingGehaltsklassenHelp('');
            $stadt->translate('fr')->setSettingGehaltsklassenHelp('');

            $stadt->translate('de')->setSettingKinderimKigaHelp('');
            $stadt->translate('en')->setSettingKinderimKigaHelp('');
            $stadt->translate('fr')->setSettingKinderimKigaHelp('');

            $stadt->translate('de')->setSettingsAnzahlKindergeldempfangerHelp('');
            $stadt->translate('en')->setSettingsAnzahlKindergeldempfangerHelp('');
            $stadt->translate('fr')->setSettingsAnzahlKindergeldempfangerHelp('');

            $stadt->translate('de')->setSettingsSozielHilfeEmpfangerHelp('');
            $stadt->translate('en')->setSettingsSozielHilfeEmpfangerHelp('');
            $stadt->translate('fr')->setSettingsSozielHilfeEmpfangerHelp('');

            foreach ($stadt->getNewTranslations() as $newTranslation) {
                if (!$stadt->getTranslations()->contains($newTranslation) && !$stadt->getNewTranslations()->isEmpty()) {
                    $stadt->addTranslation($newTranslation);
                    $stadt->getNewTranslations()->removeElement($newTranslation);
                }
            }
        }
        $builder
            ->add('name', TextType::class, ['label' => 'Name der Stadt', 'translation_domain' => 'form'])
            ->add('slug', TextType::class, ['label' => 'Slug der Stadt', 'translation_domain' => 'form'])
            ->add('active', CheckboxType::class, ['required' => false, 'label' => 'Stadt aktiv', 'translation_domain' => 'form'])
            ->add('onlineCheckinEnable', CheckboxType::class, ['required' => false, 'label' => 'Online Checkin aktivieren', 'translation_domain' => 'form'])
            ->add('ferienprogramm', CheckboxType::class, ['required' => false, 'label' => 'Wir bieten eine Ferienbetreuung über dieses Portal an', 'translation_domain' => 'form'])
            ->add('schulkindBetreung', CheckboxType::class, ['required' => false, 'label' => 'Wir bieten eine Schulkindbetreuung über dieses Portal an', 'translation_domain' => 'form'])
            ->add('email', TextType::class, ['label' => 'Email', 'translation_domain' => 'form'])
            ->add('adresse', TextType::class, ['label' => 'Straße', 'translation_domain' => 'form'])
            ->add('adresszusatz', TextType::class, ['required' => false, 'label' => 'Adresszusatz', 'translation_domain' => 'form'])
            ->add('plz', TextType::class, ['label' => 'PLZ', 'translation_domain' => 'form'])
            ->add('ort', TextType::class, ['label' => 'Stadt', 'translation_domain' => 'form'])
            ->add('telefon', TextType::class, ['label' => 'Telefonnummer', 'translation_domain' => 'form'])
            ->add('ansprechpartner', TextType::class, ['label' => 'Ansprechpartner', 'translation_domain' => 'form'])
            ->add('stadtHomepage', TextType::class, ['required' => false, 'label' => 'Homepage URL', 'translation_domain' => 'form'])
            ->add('minBlocksPerDay', NumberType::class, ['required' => true, 'label' => 'Mindestanzahl an Blöcken pro Tag', 'translation_domain' => 'form'])
            ->add('minDaysperWeek', NumberType::class, ['required' => true, 'label' => 'Mindestanzahl an Blöcken pro Woche', 'translation_domain' => 'form'])
            ->add('preiskategorien', NumberType::class, ['required' => true, 'label' => 'Anzahl der Preiskategorien', 'translation_domain' => 'form'])
            ->add('secCodeAlwaysNew', CheckboxType::class, ['required' => false, 'label' => 'Der Security-Code soll bei jeder Änderung geändert werden', 'translation_domain' => 'form'])

            //SKIB Stammdaten einstallungen
            ->add('settingsAnzahlKindergeldempfanger', CheckboxType::class, ['required' => false, 'label' => 'Abfrage Anzahl Kindergeldpflichtiger Kinder im Hausahlt', 'translation_domain' => 'form'])
            ->add('settingsAnzahlKindergeldempfangerRequired', CheckboxType::class, ['required' => false, 'label' => 'Diese Angabe ist Mandatory?', 'translation_domain' => 'form'])
            ->add('settingsSozielHilfeEmpfanger', CheckboxType::class, ['required' => false, 'label' => 'Abfrage Beziehen Sie Leistungen nach dem SGB II, SGB XII, AsylbLG, Wohngeld oder Jugendhilfe?', 'translation_domain' => 'form'])
            ->add('settingsSozielHilfeEmpfangerRequired', CheckboxType::class, ['required' => false, 'label' => 'Diese Angabe ist Mandatory?', 'translation_domain' => 'form'])
            ->add('settingGehaltsklassen', CheckboxType::class, ['required' => false, 'label' => 'Gehaltsklassen abfragen?', 'translation_domain' => 'form'])
            ->add('settingGehaltsklassenRequired', CheckboxType::class, ['required' => false, 'label' => 'Diese Angabe ist Mandatory?', 'translation_domain' => 'form'])

            ->add('settingKinderimKiga', CheckboxType::class, ['required' => false, 'label' => 'Abfrage ob weiteres Kind im KiGa?', 'translation_domain' => 'form'])


            //SKIB Stammdateneinstallungen

            ->add('showShowMoreToggleOnHomescreen', CheckboxType::class, ['required' => false, 'label' => 'Zeige den "Mehr lesen" Button auf der Startseite an', 'translation_domain' => 'form'])
            ->add('gehaltsklassen', CollectionType::class, [
                'entry_type' => TextType::class,
                'entry_options' => array('label' => 'Bezeichnung der Gehaltsklassen', 'translation_domain' => 'form')
            ])
            ->add('imageFile', VichImageType::class, [
                'required' => false,
                'allow_delete' => true,
                'delete_label' => 'Löschen',
                'label' => 'Hintergrundbild hochladen',
                'translation_domain' => 'form'
            ])
            ->add('logoStadtFile', VichImageType::class, [
                'required' => false,
                'allow_delete' => true,
                'delete_label' => 'Löschen',
                'label' => 'Logo hochladen',
                'translation_domain' => 'form'
            ])
            ->add('logoUrl', TextType::class, ['required' => false, 'label' => 'URL für Logo', 'translation_domain' => 'form'])
            ->add('hauptfarbe', TextType::class, ['required' => false, 'label' => 'Hauptfarbe(HTML Code)', 'translation_domain' => 'form'])
            ->add('akzentfarbe', TextType::class, ['required' => false, 'label' => 'Akzentfarbe (HTML Code)', 'translation_domain' => 'form'])
            ->add('akzentfarbeFehler', TextType::class, ['required' => false, 'label' => 'Akzentfarbe Fehler (HTML Code)', 'translation_domain' => 'form'])
            ->add('translations', TranslationsType::class, [

                    'fields' => [
                        'datenschutz' => [

                            'attr' => array('rows' => 6, 'class' => 'onlineEditor'),
                            'label' => 'Datenschutz',
                            'translation_domain' => 'form'
                        ],
                        'infoText' => [

                            'attr' => array('rows' => 6, 'class' => 'onlineEditor'),
                            'label' => 'Infotext',
                            'translation_domain' => 'form'
                        ],
                        'agb' => [

                            'attr' => array('rows' => 6, 'class' => 'onlineEditor'),
                            'label' => 'Allgemeine Vertragsbedingungen',
                            'translation_domain' => 'form'
                        ],
                        'catererInfo' => [

                            'attr' => array('rows' => 6,),
                            'label' => 'Information zum Caterer',
                            'translation_domain' => 'form'
                        ],
                        'careBlockInfo' => [

                            'attr' => array('rows' => 6,),
                            'label' => 'Information zum Caterer',
                            'translation_domain' => 'form'
                        ],
                        'coverText' => [

                            'attr' => array('rows' => 6, 'class' => 'onlineEditor'),
                            'label' => 'Text in der "Wichtig" Box auf der Startseite',
                            'translation_domain' => 'form'
                        ],

                        'settingKinderimKigaHelp' => [

                            'attr' => array('rows' => 1,),
                            'label' => 'Hilfetext (Text in den Fragezeigen)',
                            'translation_domain' => 'form'
                        ],
                        'settingGehaltsklassenHelp' => [

                            'attr' => array('rows' => 1,),
                            'label' => 'Hilfetext (Text in den Fragezeigen)',
                            'translation_domain' => 'form'
                        ],
                        'settingsSozielHilfeEmpfangerHelp' => [

                            'attr' => array('rows' => 1,),
                            'label' => 'Hilfetext (Text in den Fragezeigen)',
                            'translation_domain' => 'form'
                        ],
                        'settingsAnzahlKindergeldempfangerHelp' => [

                            'attr' => array('rows' => 1,),
                            'label' => 'Hilfetext (Text in den Fragezeigen)',
                            'translation_domain' => 'form'
                        ]
                    ]
                ]
            )
            ->add('imprint', TextareaType::class, ['attr' => ['rows' => 6, 'class' => 'onlineEditor'], 'required' => true, 'label' => 'Impressum der Stadt', 'translation_domain' => 'form'])
            ->add('submit', SubmitType::class, ['label' => 'Speichern', 'translation_domain' => 'form']);

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Stadt::class,
            'gehaltsklasse' => 1,
        ]);
        $resolver->setAllowedTypes('gehaltsklasse', 'integer');
    }
}

