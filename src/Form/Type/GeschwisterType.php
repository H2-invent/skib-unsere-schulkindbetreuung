<?php
/**
 * Created by PhpStorm.
 * User: Emanuel
 * Date: 17.09.2019
 * Time: 20:29
 */

namespace App\Form\Type;


use App\Entity\Geschwister;
use App\Entity\Stadt;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GeschwisterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $today = intval((new \DateTime())->format('Y'));

        $builder
            ->add('vorname', TextType::class, ['label' => 'Vorname', 'translation_domain' => 'form'])
            ->add('nachname', TextType::class, ['label' => 'Nachname', 'translation_domain' => 'form'])
            ->add('geburtsdatum', BirthdayType::class, ['attr'=>array('class'=>'pickadate'),'widget'=>'single_text','years'=>range($today-20,$today,1),'label' => 'Geburtstag', 'translation_domain' => 'form']);
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Geschwister::class,
            'stadt' => Stadt::class
        ]);

    }
}

