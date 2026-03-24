<?php

namespace App\Form\Type;

use App\Entity\Active;
use App\Entity\ParentSickPortalAccess;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;

class ParentSickAccessRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $schuljahre = $options['schuljahre'];
        usort($schuljahre, static function (Active $a, Active $b) {
            return $b->getBis() <=> $a->getBis();
        });

        $builder
            ->add('schuljahr', EntityType::class, [
                'class' => Active::class,
                'choices' => $schuljahre,
                'choice_label' => static function (Active $schuljahr) {
                    return $schuljahr->getVon()->format('Y') . '/' . $schuljahr->getBis()->format('Y');
                },
                'placeholder' => 'Bitte wählen',
                'label' => 'Schuljahr',
                'data' => $schuljahre[0] ?? null,
            ])
            ->add('email', TextType::class, [
                'label' => 'E-Mail-Adresse',
                'constraints' => [new Email()],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Link zusenden',
                'attr' => ['class' => 'btn btn-primary mt-3'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ParentSickPortalAccess::class,
            'schuljahre' => [],
        ]);
    }
}
