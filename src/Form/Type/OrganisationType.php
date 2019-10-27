<?php
/**
 * Created by PhpStorm.
 * User: Emanuel
 * Date: 17.09.2019
 * Time: 20:29
 */
namespace App\Form\Type;

use App\Entity\Organisation;
use App\Entity\Stadt;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichFileType;
use Vich\UploaderBundle\Form\Type\VichImageType;

class OrganisationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class,['label'=>'Name der Organisation','translation_domain' => 'form'])
            ->add('adresse', TextType::class,['label'=>'Straße','translation_domain' => 'form'])
            ->add('adresszusatz', TextType::class,['required'=>false,'label'=>'Adresszusatz','translation_domain' => 'form'])
            ->add('plz', TextType::class,['label'=>'PLZ','translation_domain' => 'form'])
            ->add('ort', TextType::class,['label'=>'Stadt','translation_domain' => 'form'])
            ->add('ansprechpartner', TextType::class,['label'=>'Ansprechpartner','translation_domain' => 'form'])
            ->add('iban', TextType::class,['label'=>'IBAN für das Lastschriftmandat','translation_domain' => 'form'])
            ->add('bic', TextType::class,['label'=>'BIC','translation_domain' => 'form'])
            ->add('bankName', TextType::class,['label'=>'Name der Bank','translation_domain' => 'form'])
            ->add('glauaubigerId', TextType::class,['label'=>'Gläubiger ID','translation_domain' => 'form'])
            ->add('telefon', TextType::class,['label'=>'Telefonnummer','translation_domain' => 'form'])
            ->add('email', TextType::class,['label'=>'Email','translation_domain' => 'form'])
            ->add('infoText', TextareaType::class,['label'=>'Info Text','translation_domain' => 'form','attr'=>['rows'=>3]])
            ->add('imageFile', VichImageType::class, [
                'required' => false,
                'allow_delete' => true,
                'delete_label' => 'Löschen',
                'label'=>'Logo hochladen',
                'translation_domain' => 'form'
            ])
            ->add('smptServer', TextType::class,['required'=>false,'label'=>'SMTP Server','translation_domain' => 'form'])
            ->add('smtpPort', TextType::class,['required'=>false,'label'=>'SMTP Port','translation_domain' => 'form'])
            ->add('smtpUser', TextType::class,['required'=>false,'label'=>'SMTP Username','translation_domain' => 'form'])
            ->add('smtpPassword', TextType::class,['required'=>false,'label'=>'SMTP Passwort','translation_domain' => 'form'])
            ->add('datenschutzDE', TextareaType::class, ['required'=>false,'label'=>'Datenschutzhinweis Deutsch','translation_domain' => 'form', 'property_path' => 'translations[de].datenschutz', ])
            ->add('datenschutzEN', TextareaType::class, ['required'=>false,'label'=>'Datenschutzhinweis Englisch','translation_domain' => 'form','property_path' => 'translations[en].datenschutz', ])
            ->add('datenschutzFR', TextareaType::class, ['required'=>false,'label'=>'Datenschutzhinweis Französisch','translation_domain' => 'form','property_path' => 'translations[fr].datenschutz', ])
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