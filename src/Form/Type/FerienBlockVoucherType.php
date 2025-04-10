<?php
/**
 * Created by PhpStorm.
 * User: Emanuel
 * Date: 17.09.2019
 * Time: 20:29
 */

namespace App\Form\Type;

use App\Entity\Active;

use App\Entity\Ferienblock;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FerienBlockVoucherType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {


        $builder
              ->add('voucher', CollectionType::class, [
                'entry_type' => TextType::class,
                'entry_options' => array('required'=>true, 'label' => 'Gutschein', 'translation_domain' => 'form'),
            ])
            ->add('voucherPrice', CollectionType::class, [
                'entry_type' => NumberType::class,
                'entry_options' => array('required'=>true, 'label' => 'Preise', 'translation_domain' => 'form')
            ])
            ->add('save', SubmitType::class, ['label' => 'Speichern', 'translation_domain' => 'form']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Ferienblock::class,
            'voucher' => 1,
            'voucherPrice' => 1,
        ]);
        $resolver->setAllowedTypes('voucher', 'integer');
        $resolver->setAllowedTypes('voucherPrice', 'integer');
    }
}
