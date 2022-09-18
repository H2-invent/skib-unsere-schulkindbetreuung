<?php
/**
 * Created by PhpStorm.
 * User: Emanuel
 * Date: 17.09.2019
 * Time: 20:29
 */

namespace App\Form\Type;

use App\Entity\Active;

use App\Entity\Kind;
use App\Entity\News;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChildChangeSchoolyearType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $stadt = $options['kind']->getSchule()->getStadt();
        $builder
            ->add('schoolyear', ChoiceType::class, ['choices' => array_flip($stadt->translate()->getSettingsSkibShoolyearNamingArray()), 'label' => 'Bitte das neue Schuljahr auswählen.', 'translation_domain' => 'form'])
            ->add('submit', SubmitType::class, ['attr' => array('class' => 'btn btn-outline-primary'), 'label' => 'Speichern', 'translation_domain' => 'form']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'kind' => Kind::class,
        ]);
    }
}
