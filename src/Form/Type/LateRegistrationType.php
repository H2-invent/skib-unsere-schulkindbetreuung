<?php

namespace App\Form\Type;

use App\Entity\Active;
use App\Entity\LateRegistration;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;

class LateRegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $schuljahre = $options['schuljahre'];
        // sort schuljahre options for newest first
        usort($schuljahre, static function (Active $a, Active $b) {
            return $b->getBis() <=> $a->getBis();
        });

        $builder
            ->add('email', TextType::class, [
                'label' => 'Email',
                'constraints' => new Email(),
            ])
            ->add('schuljahr', EntityType::class, [
                'class' => Active::class,
                'choices' => $schuljahre,
                'choice_label' => function (Active $schuljahr) {
                    return $schuljahr->getVon()->format('Y') . '/' . $schuljahr->getBis()->format('Y');
                },
                'label' => 'Schuljahr',
                'placeholder' => 'Bitte wählen',
                'data' => $schuljahre[0],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Mail schicken',
                'attr' => [
                    'class' => 'btn btn-primary mt-3',
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => LateRegistration::class,
            'schuljahre' => [],
        ]);
    }
}
