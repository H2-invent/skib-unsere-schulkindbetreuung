<?php

/**
 * Created by PhpStorm.
 * User: Emanuel
 * Date: 17.09.2019
 * Time: 20:29.
 */

namespace App\Form\Type;

use App\Entity\Schule;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class, ['label' => 'Email', 'required' => true, 'translation_domain' => 'form'])
            ->add('schulen', EntityType::class, [
                'class' => Schule::class,
                'choice_label' => fn (Schule $schule) => $schule->getName(),
                'label' => 'Zugeordnete Schulen ',
                'translation_domain' => 'form',
                'multiple' => true,
                'expanded' => true,
                'choices' => $options['schulen'],
            ])
            ->add('birthday', BirthdayType::class, ['widget' => 'single_text', 'required' => false, 'label' => 'Geburtstag', 'translation_domain' => 'form'])
            ->add('save', SubmitType::class, ['label' => 'Speichern', 'translation_domain' => 'form'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'schulen' => [],
        ]);
    }
}
