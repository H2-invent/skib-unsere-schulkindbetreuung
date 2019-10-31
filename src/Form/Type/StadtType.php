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
            ->add('email', TextType::class,['label'=>'Email','translation_domain' => 'form'])
            ->add('adresse', TextType::class,['label'=>'Straße','translation_domain' => 'form'])
            ->add('adresszusatz', TextType::class,['required'=>false,'label'=>'Adresszusatz','translation_domain' => 'form'])
            ->add('plz', TextType::class,['label'=>'PLZ','translation_domain' => 'form'])
            ->add('ort', TextType::class,['label'=>'Stadt','translation_domain' => 'form'])
            ->add('telefon', TextType::class,['label'=>'Telefonnummer','translation_domain' => 'form'])
            ->add('ansprechpartner', TextType::class,['label'=>'Ansprechpartner','translation_domain' => 'form'])
            ->add('preiskategorien', NumberType::class,['required'=>true,'label'=>'Anzahl der Preiskategorien','translation_domain' => 'form'])
            ->add('imageFile', VichImageType::class, [
                'required' => false,
                'allow_delete' => true,
                'delete_label' => 'Löschen',
                'label'=>'Logo hochladen',
                'translation_domain' => 'form'
            ])
            ->add('logoUrl', TextType::class,['required'=>false,'label'=>'URL für Logo','translation_domain' => 'form'])
            ->add('smtpServer', TextType::class,['required'=>false,'label'=>'SMTP Server','translation_domain' => 'form'])
            ->add('smtpPort', TextType::class,['required'=>false,'label'=>'SMTP Port','translation_domain' => 'form'])
            ->add('smtpUsername', TextType::class,['required'=>false,'label'=>'SMTP Username','translation_domain' => 'form'])
            ->add('smtpPassword', TextType::class,['required'=>false,'label'=>'SMTP Passwort','translation_domain' => 'form'])
            ->add('hauptfarbe', TextType::class,['required'=>false,'label'=>'Hauptfarbe(HTML Code)','translation_domain' => 'form'])
            ->add('akzentfarbe', TextType::class,['required'=>false,'label'=>'Akzentfarbe (HTML Code)','translation_domain' => 'form'])
            ->add('akzentfarbeFehler', TextType::class,['required'=>false,'label'=>'Akzentfarbe Fehler (HTML Code)','translation_domain' => 'form'])
            ->add('infoTextDe', TextareaType::class, ['attr'=>['rows'=>6],'required'=>false,'label'=>'Info Text Deutsch ','translation_domain' => 'form', 'property_path' => 'translations[de].infoText', ])
            ->add('infoTextEn', TextareaType::class, ['attr'=>['rows'=>6],'required'=>false,'label'=>'Info Text Englisch','translation_domain' => 'form','property_path' => 'translations[en].infoText', ])
            ->add('infoTextFr', TextareaType::class, ['attr'=>['rows'=>6],'required'=>false,'label'=>'Info Text Französisch','translation_domain' => 'form','property_path' => 'translations[fr].infoText', ])
            ->add('infoAGBDe', TextareaType::class, ['attr'=>['rows'=>6],'required'=>false,'label'=>'AGB Deutsch (Markdown)','translation_domain' => 'form', 'property_path' => 'translations[de].agb', ])
            ->add('infoAGBEn', TextareaType::class, ['attr'=>['rows'=>6],'required'=>false,'label'=>'AGB Englisch (Markdown)','translation_domain' => 'form','property_path' => 'translations[en].agb', ])
            ->add('infoAGBFr', TextareaType::class, ['attr'=>['rows'=>6],'required'=>false,'label'=>'AGB Französisch (Markdown)','translation_domain' => 'form','property_path' => 'translations[fr].agb', ])
            ->add('submit', SubmitType::class, ['label' => 'Speichern','translation_domain' => 'form'])

        ;
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Stadt::class,
        ]);
    }
}
