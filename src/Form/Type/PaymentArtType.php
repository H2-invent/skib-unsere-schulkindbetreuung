<?php
/**
 * Created by PhpStorm.
 * User: Emanuel
 * Date: 17.09.2019
 * Time: 20:29
 */

namespace App\Form\Type;

use App\Entity\Active;

use App\Entity\Organisation;
use App\Entity\Payment;
use App\Entity\PaymentSepa;
use App\Entity\Zeitblock;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;

use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
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

class PaymentArtType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('braintreeSandbox', CheckboxType::class, array( 'required'=>false,'label' => 'Braintree in der Sandbox', 'translation_domain' => 'form'))
            ->add('braintreeOK', CheckboxType::class, array( 'required'=>false,'label' => 'Braintree akzeptieren', 'translation_domain' => 'form'))
            ->add('braintreeMerchantId', TextType::class, array( 'required'=>false,'label' => 'Braintree Merchant ID', 'translation_domain' => 'form'))
            ->add('braintreePrivateKey', TextType::class, array( 'required'=>false,'label' => 'Braintree Private Key', 'translation_domain' => 'form'))
            ->add('braintreePublicKey', TextType::class, array( 'required'=>false,'label' => 'Braintree Public Key', 'translation_domain' => 'form'))
            ->add('stripeOK', CheckboxType::class, array( 'required'=>false,'label' => 'Stripe akzeptieren', 'translation_domain' => 'form'))
            ->add('stripeID', TextType::class, array( 'required'=>false,'label' => 'Stripe Public Key', 'translation_domain' => 'form'))
            ->add('stripeSecret', TextType::class, array( 'required'=>false,'label' => 'Stripe Private Key', 'translation_domain' => 'form'))

            ->add('save', SubmitType::class, ['label' => 'Weiter', 'translation_domain' => 'form']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Organisation::class
        ]);

    }
}
