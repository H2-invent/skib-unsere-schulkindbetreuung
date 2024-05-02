<?php
/**
 * Created by PhpStorm.
 * User: Emanuel
 * Date: 17.09.2019
 * Time: 20:29
 */

namespace App\Form\Type;


use App\Entity\Stammdaten;
use Symfony\Component\Form\AbstractType;

use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class SupportForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('name',TextType::class,['required' => true,'disabled'=>true, 'label' => 'Name', 'translation_domain' => 'form'])
            ->add('email',TextType::class,['required' => true,'disabled'=>true, 'label' => 'Email', 'translation_domain' => 'form'])
            ->add('user',TextType::class,['required' => true,'disabled'=>true, 'label' => 'Username', 'translation_domain' => 'form'])
            ->add('phone', TextType::class, ['required' => true, 'label' => 'Telefonnummer', 'translation_domain' => 'form'])
            ->add('subject', TextType::class, ['required' => true, 'label' => 'Betreff', 'translation_domain' => 'form'])
            ->add('message', TextareaType::class, ['attr'=>array('class'=>'onlineEditor','rows'=>10),'required' => true, 'label' => 'Nachricht', 'translation_domain' => 'form'])
            ->add('submit', SubmitType::class, ['attr' => array('class' => 'btn btn-primary'), 'label' => 'Speichern', 'translation_domain' => 'form']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([

        ]);

    }
}
