<?php
/**
 * Created by PhpStorm.
 * User: Emanuel
 * Date: 17.09.2019
 * Time: 20:29
 */

namespace App\Form\Type;

use App\Entity\Zeitblock;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BlockAbhangigkeitType extends AbstractType
{


    public function __construct()
    {

    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $vorgangerProperty = $options['silent'] ? 'vorgangerSilent' : 'vorganger';
        $builder
            ->add('vorganger', EntityType::class, [
                'class' => Zeitblock::class,
                'choice_label' => fn(Zeitblock $zeitblock) => $zeitblock->getVon()->format('H:i') . '-' . $zeitblock->getBis()->format('H:i'),
                'placeholder' => 'Ganztag oder Halbtag muss gewählt sein',
                'label' => 'Muss auch gewählt sein (strg halten für Mehrfachauswahl)',
                'translation_domain' => 'form',
                'multiple' => true,
                'expanded' => false,
                'choices' => $options['blocks'],
                'group_by' => fn(Zeitblock $zeitblock, $key, $value) => $zeitblock->getWochentagString(),
                'property_path' => $vorgangerProperty,
            ])
        ;
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Zeitblock::class,
            'anzahlPreise' => 1,
            'blocks' => [],
            'silent' => false,
        ]);
        $resolver->setAllowedTypes('anzahlPreise', 'integer');
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'appbundle_zeitblock';
    }
}
