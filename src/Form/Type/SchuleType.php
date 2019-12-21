<?php

namespace App\Form\Type;

use App\Entity\Organisation;
use App\Entity\Schule;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;

class SchuleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class,['label'=>'Name der Schule','translation_domain' => 'form'])
            ->add('adresse', TextType::class,['label'=>'Straße','translation_domain' => 'form'])
            ->add('adresszusatz', TextType::class,['required'=>false,'label'=>'Adresszusatz','translation_domain' => 'form'])
            ->add('plz', TextType::class,['label'=>'PLZ','translation_domain' => 'form'])
            ->add('ort', TextType::class,['label'=>'Stadt','translation_domain' => 'form'])
            ->add('catererName', TextType::class,['required'=>false,'label'=>'Caterer','translation_domain' => 'form'])
            ->add('catererEmail', TextType::class,['required'=>false,'label'=>'Caterer Email','translation_domain' => 'form'])
            ->add('catererUrl', TextType::class,['required'=>false,'label'=>'Caterer URL','translation_domain' => 'form'])
            ->add('infoText', TextareaType::class,['label'=>'Info Text','translation_domain' => 'form','attr'=>array('rows'=>6)])

            ->add('organisation', EntityType::class, [
                'choice_label' => 'name',
                'class' => Organisation::class,
                'choices' => $options['organisations'],
            ])
            ->add('imageFile', VichImageType::class, [
                'required' => false,
                'allow_delete' => true,
                'delete_label' => 'Löschen',
                'label'=>'Logo hochladen',
                'translation_domain' => 'form'
            ])
            ->add('submit', SubmitType::class, ['label' => 'Speichern','translation_domain' => 'form'])

        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Schule::class,
            'organisations'=>null
        ]);
    }
}
