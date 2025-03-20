<?php

namespace App\Form;

use App\Entity\Active;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\RadioType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class KvjsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $schuljahre = $options['schuljahre'] ?? [];
        $builder
            ->add('schuljahr', EntityType::class, [
                'class' => Active::class,
                'choices'=>$schuljahre,
                'choice_label' => function (Active $schuljahr) {
                dump($schuljahr);
                    return sprintf('%s/%s', $schuljahr->getVon()->format('Y'), $schuljahr->getBis()->format('Y'));
                },
                'label' => 'Schuljahr',
                'placeholder' => 'Bitte wählen',
                'required' => true,
            ])
            ->add('datum', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Stichtag auswählen',
                'required' => true,

            ])
            ->add('type',ChoiceType::class,[
                'label' => 'Betreuungstyp in Ihrer Organisation',
                'required' => true,
                'expanded'=>true,
                'multiple'=>false,
                'choices'=>[
                    'B3: Kindertageseinrichtungen, Horte und Horte an der Schule mit Betriebser-
laubnis gemäß § 45 SGB VII'=>'b3',
                    ' B4: Betreuungsangebote in kommunaler oder freier Trägerschaft gemäß § 8b
SchG'=>'b4'
                ]
            ])
        ->add('submit',SubmitType::class,[
            'label'=>'Erstellen',
            'attr'=>[
                'class'=>'btn btn-primary mt-3'
            ]
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
            'schuljahre' => [],
        ]);
    }
}
