<?php
/**
 * Created by PhpStorm.
 * User: Emanuel
 * Date: 17.09.2019
 * Time: 20:29
 */
namespace App\Form\Type;


use App\Entity\Stadt;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichFileType;
use Vich\UploaderBundle\Form\Type\VichImageType;

class StadtType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $stadt = $options['data'];
        if($stadt->getTranslations()->isEmpty()){
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

            foreach ($stadt->getNewTranslations() as $newTranslation) {
                if (!$stadt->getTranslations()->contains($newTranslation) && !$stadt->getNewTranslations()->isEmpty()) {
                    $stadt->addTranslation($newTranslation);
                    $stadt->getNewTranslations()->removeElement($newTranslation);
                }
            }
        }
        $builder
            ->add('name', TextType::class,['label'=>'Name der Stadt','translation_domain' => 'form'])
            ->add('slug', TextType::class,['label'=>'Slug der Stadt','translation_domain' => 'form'])
            ->add('active', CheckboxType::class,['required'=>false,'label'=>'Stadt aktiv','translation_domain' => 'form'])
            ->add('ferienprogramm', CheckboxType::class,['required'=>false,'label'=>'Wir bieten eine Ferienbetreuung über dieses Portal an','translation_domain' => 'form'])
            ->add('schulkindBetreung', CheckboxType::class,['required'=>false,'label'=>'Wir bieten eine Schulkindbetreuung über dieses Portal an','translation_domain' => 'form'])
            ->add('email', TextType::class,['label'=>'Email','translation_domain' => 'form'])
            ->add('adresse', TextType::class,['label'=>'Straße','translation_domain' => 'form'])
            ->add('adresszusatz', TextType::class,['required'=>false,'label'=>'Adresszusatz','translation_domain' => 'form'])
            ->add('plz', TextType::class,['label'=>'PLZ','translation_domain' => 'form'])
            ->add('ort', TextType::class,['label'=>'Stadt','translation_domain' => 'form'])
            ->add('telefon', TextType::class,['label'=>'Telefonnummer','translation_domain' => 'form'])
            ->add('ansprechpartner', TextType::class,['label'=>'Ansprechpartner','translation_domain' => 'form'])
            ->add('stadtHomepage', TextType::class,['required'=>false,'label'=>'Homepage URL','translation_domain' => 'form'])
            ->add('minBlocksPerDay', NumberType::class,['required'=>true,'label'=>'Mindestanzahl an Blöcken pro Tag','translation_domain' => 'form'])
            ->add('minDaysperWeek', NumberType::class,['required'=>true,'label'=>'Mindestanzahl an Blöcken pro Woche','translation_domain' => 'form'])

            ->add('preiskategorien', NumberType::class,['required'=>true,'label'=>'Anzahl der Preiskategorien','translation_domain' => 'form'])
            ->add('gehaltsklassen', CollectionType::class, [
                'entry_type' => TextType::class,
                'entry_options' => array('label' => 'Bezeichnung der Gehaltsklassen', 'translation_domain' => 'form')
            ])
            ->add('imageFile', VichImageType::class, [
                'required' => false,
                'allow_delete' => true,
                'delete_label' => 'Löschen',
                'label'=>'Logo hochladen',
                'translation_domain' => 'form'
            ])
            ->add('logoUrl', TextType::class,['required'=>false,'label'=>'URL für Logo','translation_domain' => 'form'])
            ->add('hauptfarbe', TextType::class,['required'=>false,'label'=>'Hauptfarbe(HTML Code)','translation_domain' => 'form'])
            ->add('akzentfarbe', TextType::class,['required'=>false,'label'=>'Akzentfarbe (HTML Code)','translation_domain' => 'form'])
            ->add('akzentfarbeFehler', TextType::class,['required'=>false,'label'=>'Akzentfarbe Fehler (HTML Code)','translation_domain' => 'form'])
            ->add('infoTextDe', TextareaType::class, ['attr'=>['rows'=>6,'class'=>'onlineEditor'],'required'=>false,'label'=>'Info Text Deutsch ','translation_domain' => 'form', 'property_path' => 'translations[de].infoText', ])
            ->add('infoTextEn', TextareaType::class, ['attr'=>['rows'=>6,'class'=>'onlineEditor'],'required'=>false,'label'=>'Info Text Englisch','translation_domain' => 'form','property_path' => 'translations[en].infoText', ])
            ->add('infoTextFr', TextareaType::class, ['attr'=>['rows'=>6,'class'=>'onlineEditor'],'required'=>false,'label'=>'Info Text Französisch','translation_domain' => 'form','property_path' => 'translations[fr].infoText', ])
            ->add('infoAGBDe', TextareaType::class, ['attr'=>['rows'=>6,'class'=>'onlineEditor'],'required'=>true,'label'=>'Vertragsbedingungen Deutsch (Markdown)','translation_domain' => 'form', 'property_path' => 'translations[de].agb', ])
            ->add('infoAGBEn', TextareaType::class, ['attr'=>['rows'=>6,'class'=>'onlineEditor'],'required'=>true,'label'=>'Vertragsbedingungen Englisch (Markdown)','translation_domain' => 'form','property_path' => 'translations[en].agb', ])
            ->add('infoAGBFr', TextareaType::class, ['attr'=>['rows'=>6,'class'=>'onlineEditor'],'required'=>true,'label'=>'Vertragsbedingungen Französisch (Markdown)','translation_domain' => 'form','property_path' => 'translations[fr].agb', ])
            ->add('datenschutzDE', TextareaType::class, ['attr'=>['rows'=>6,'class'=>'onlineEditor'],'required'=>true,'label'=>'Datenschutzhinweis Deutsch (Markdown)','translation_domain' => 'form', 'property_path' => 'translations[de].datenschutz', ])
            ->add('datenschutzEn', TextareaType::class, ['attr'=>['rows'=>6,'class'=>'onlineEditor'],'required'=>true,'label'=>'Datenschutzhinweis Englisch (Markdown)','translation_domain' => 'form','property_path' => 'translations[en].datenschutz', ])
            ->add('datenschutzFr', TextareaType::class, ['attr'=>['rows'=>6,'class'=>'onlineEditor'],'required'=>true,'label'=>'Datenschutzhinweis Französisch (Markdown)','translation_domain' => 'form','property_path' => 'translations[fr].datenschutz', ])
            ->add('careBlockInfoDE', TextareaType::class, ['attr'=>['rows'=>3],'required'=>true,'label'=>'Betreuungszeitfenster Info Deutsch (Markdown)','translation_domain' => 'form', 'property_path' => 'translations[de].careBlockInfo', ])
            ->add('careBlockInfoEn', TextareaType::class, ['attr'=>['rows'=>3],'required'=>true,'label'=>'Betreuungszeitfenster Info Englisch (Markdown)','translation_domain' => 'form','property_path' => 'translations[en].careBlockInfo', ])
            ->add('careBlockInfoFr', TextareaType::class, ['attr'=>['rows'=>3],'required'=>true,'label'=>'Betreuungszeitfenster Info Französisch (Markdown)','translation_domain' => 'form','property_path' => 'translations[fr].careBlockInfo', ])
            ->add('catererInfoDE', TextareaType::class, ['attr'=>['rows'=>6,'class'=>'onlineEditor'],'required'=>true,'label'=>'Caterer Info Deutsch (Markdown)','translation_domain' => 'form', 'property_path' => 'translations[de].catererInfo', ])
            ->add('catererInfoEn', TextareaType::class, ['attr'=>['rows'=>6,'class'=>'onlineEditor'],'required'=>true,'label'=>'Caterer Info Englisch (Markdown)','translation_domain' => 'form','property_path' => 'translations[en].catererInfo', ])
            ->add('catererInfoFr', TextareaType::class, ['attr'=>['rows'=>6,'class'=>'onlineEditor'],'required'=>true,'label'=>'Caterer Info Französisch (Markdown)','translation_domain' => 'form','property_path' => 'translations[fr].catererInfo', ])

            ->add('imprint', TextareaType::class,['attr'=>['rows'=>6,'class'=>'onlineEditor'],'required'=>true,'label'=>'Impressum der Stadt (Markdown)','translation_domain' => 'form'])

            ->add('submit', SubmitType::class, ['label' => 'Speichern','translation_domain' => 'form'])

        ;
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Stadt::class,
            'gehaltsklasse'=>1,
        ]);
        $resolver->setAllowedTypes('gehaltsklasse', 'integer');
    }
}

