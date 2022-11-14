<?php

namespace App\Form\Type;

use App\Entity\Kind;
use App\Entity\Organisation;
use App\Entity\Schule;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;

class InternNoticeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('internalNotice', TextareaType::class,['label'=>'Interne Notiz. (Diese Notiz ist auch auf dem PDF zu sehen)','translation_domain' => 'form','attr'=> array('rows' => 6, 'class' => 'onlineEditor'),])
            ->add('submit', SubmitType::class, ['label' => 'Speichern','translation_domain' => 'form'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Kind::class,
        ]);
    }
}
