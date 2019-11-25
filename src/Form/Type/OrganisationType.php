<?php
/**
 * Created by PhpStorm.
 * User: Emanuel
 * Date: 17.09.2019
 * Time: 20:29
 */
namespace App\Form\Type;

use App\Entity\Organisation;
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

class OrganisationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $org = $options['data'];
        if($org->getTranslations()->isEmpty()){
            $org->translate('de')->setDatenschutz('');
            $org->translate('en')->setDatenschutz('');
            $org->translate('fr')->setDatenschutz('');

            foreach ($org->getNewTranslations() as $newTranslation) {
                if (!$org->getTranslations()->contains($newTranslation) && !$org->getNewTranslations()->isEmpty()) {
                    $org->addTranslation($newTranslation);
                    $org->getNewTranslations()->removeElement($newTranslation);
                }
            }
        }

        $builder
            ->add('name', TextType::class,['label'=>'Name der Organisation','translation_domain' => 'form'])
            ->add('ferienprogramm', CheckboxType::class,['required'=>false,'label'=>'Wir bieten eine Ferienbetreuung über dieses Portal an','translation_domain' => 'form'])
            ->add('adresse', TextType::class,['label'=>'Straße','translation_domain' => 'form'])
            ->add('adresszusatz', TextType::class,['required'=>false,'label'=>'Adresszusatz','translation_domain' => 'form'])
            ->add('plz', TextType::class,['label'=>'PLZ','translation_domain' => 'form'])
            ->add('ort', TextType::class,['label'=>'Stadt','translation_domain' => 'form'])
            ->add('ansprechpartner', TextType::class,['label'=>'Ansprechpartner','translation_domain' => 'form'])
            ->add('iban', TextType::class,['label'=>'IBAN für das Lastschriftmandat','translation_domain' => 'form'])
            ->add('bic', TextType::class,['label'=>'BIC','translation_domain' => 'form'])
            ->add('bankName', TextType::class,['label'=>'Name der Bank','translation_domain' => 'form'])
            ->add('glauaubigerId', TextType::class,['label'=>'Gläubiger ID','translation_domain' => 'form'])
            ->add('steuernummer', TextType::class,['label'=>'Steuernummer','translation_domain' => 'form'])
            ->add('umstid', TextType::class,['label'=>'UmSt. Identnummer','translation_domain' => 'form'])
            ->add('telefon', TextType::class,['label'=>'Telefonnummer','translation_domain' => 'form'])
            ->add('email', TextType::class,['label'=>'Email','translation_domain' => 'form'])
            ->add('orgHomepage', TextType::class,['required'=>false,'label'=>'Homepage URL','translation_domain' => 'form'])
            ->add('infoText', TextareaType::class,['label'=>'Info Text','translation_domain' => 'form','attr'=>['rows'=>3]])
            ->add('imageFile', VichImageType::class, [
                'required' => false,
                'allow_delete' => true,
                'delete_label' => 'Löschen',
                'label'=>'Logo hochladen',
                'translation_domain' => 'form'
            ])
            ->add('datenschutzDE', TextareaType::class, ['attr'=>['rows'=>6],'required'=>false,'label'=>'Datenschutzhinweis Deutsch (Markdown)','translation_domain' => 'form', 'property_path' => 'translations[de].datenschutz', ])
            ->add('datenschutzEN', TextareaType::class, ['attr'=>['rows'=>6],'required'=>false,'label'=>'Datenschutzhinweis Englisch (Markdown)','translation_domain' => 'form','property_path' => 'translations[en].datenschutz', ])
            ->add('datenschutzFR', TextareaType::class, ['attr'=>['rows'=>6],'required'=>false,'label'=>'Datenschutzhinweis Französisch (Markdown)','translation_domain' => 'form','property_path' => 'translations[fr].datenschutz', ])

            ->add('submit', SubmitType::class, ['label' => 'Speichern','translation_domain' => 'form'])
        ;
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Organisation::class,
        ]);
    }
}
