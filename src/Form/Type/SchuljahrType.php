<?php
/**
 * Created by PhpStorm.
 * User: Emanuel
 * Date: 17.09.2019
 * Time: 20:29
 */
namespace App\Form\Type;

use App\Entity\Active;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SchuljahrType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('von', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Schuljahresbegin',
                'required' => true,
                'translation_domain' => 'form'
            ])
            ->add('bis', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Schuljahresende',
                'required' => true,
                'translation_domain' => 'form'
            ])
            ->add('anmeldeStart', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Beginn der Anmeldung',
                'required' => true,
                'translation_domain' => 'form'
            ])
            ->add('anmeldeEnde', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Ende der Anmeldung',
                'required' => true,
                'translation_domain' => 'form'
            ]);

        // Bedingte Anzeige des Felds
        if ($options['user_changed'] && in_array('ROLE_ADMIN', $options['previous_roles'] ?? [])) {
            $builder->add('specialCalculationFormular', TextareaType::class, [
                'label' => 'Berechnungsformel nur für dieses Schuljahr gültig (optional)',
                'required' => false,
                'translation_domain' => 'form',
            ]);
        }

        $builder->add('save', SubmitType::class, [
            'label' => 'Speichern',
            'translation_domain' => 'form',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Active::class,
            'user_changed' => false,        // Standardwert
            'previous_roles' => [],         // Standardwert
        ]);
    }
}
