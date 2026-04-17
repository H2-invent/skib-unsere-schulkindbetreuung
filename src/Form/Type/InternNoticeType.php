<?php

namespace App\Form\Type;

use App\Entity\Kind;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InternNoticeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('internalNotice', TextareaType::class, ['required' => false, 'label' => 'Interne Notiz. (Diese Notiz ist auch auf dem PDF zu sehen)', 'translation_domain' => 'form', 'attr' => ['rows' => 6, 'class' => 'onlineEditor']])
            ->add('submit', SubmitType::class, ['label' => 'Speichern', 'translation_domain' => 'form'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Kind::class,
        ]);
    }
}
