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
use App\Entity\StadtTranslation;
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

class StadtRechtlichType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {


        $builder
            ->add('agb', TranslationsType::class, ['attr' => ['rows' => 6, 'class' => 'onlineEditor'], 'required' => true, 'label' => 'Vertragsbedingungen ', 'translation_domain' => 'form'])
            ->add('datenschutz', TranslationsType::class, ['attr' => ['rows' => 6, 'class' => 'onlineEditor'], 'required' => true, 'label' => 'Datenschutzhinweis', 'translation_domain' => 'form']);
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

