<?php
/**
 * Created by PhpStorm.
 * User: Emanuel
 * Date: 17.09.2019
 * Time: 20:29
 */
namespace App\Form\Type;

use App\Entity\Active;

use App\Entity\Payment;
use App\Entity\PaymentSepa;
use App\Entity\Zeitblock;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;

use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichFileType;
use Vich\UploaderBundle\Form\Type\VichImageType;

class PaymentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('kontoinhaber', NumberType::class,array('label'=>'Kontoinhaber für das Lastschriftmandat','required'=>false,'translation_domain' => 'form'))
            ->add('iban', TextType::class,array('label'=>'IBAN für das Lastschriftmandat','required'=>true,'translation_domain' => 'form'))
            ->add('bic', TextType::class,array('label'=>'BIC für das Lastschriftmandat','required'=>true,'translation_domain' => 'form'))
            ->add('bankName', TextType::class,array('label'=>'Name der Bank','required'=>false,'translation_domain' => 'form'))
            ->add('save', SubmitType::class, ['label' => 'Weiter','translation_domain' => 'form'])
        ;
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PaymentSepa::class
        ]);

    }
}
