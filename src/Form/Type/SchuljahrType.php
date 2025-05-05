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
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichFileType;
use Vich\UploaderBundle\Form\Type\VichImageType;

class SchuljahrType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('von', DateType::class,array('widget'=>'single_text','label'=>'Schuljahresbegin','required'=>true,'translation_domain' => 'form'))
            ->add('bis', DateType::class,array('widget'=>'single_text','label'=>'Schuljahresende','required'=>true,'translation_domain' => 'form'))
            ->add('anmeldeStart', DateType::class,array('widget'=>'single_text','label'=>'Beginn der Anmeldung','required'=>true,'translation_domain' => 'form'))
            ->add('anmeldeEnde', DateType::class,array('widget'=>'single_text','label'=>'Ende der Anmeldung','required'=>true,'translation_domain' => 'form'))
            ->add('specialCalculationFormular', TextareaType::class,array('label'=>'Berechnungsformel nur für dieses Schuljahr gültig (optional)','required'=>false,'translation_domain' => 'form'))
            ->add('save', SubmitType::class, ['label' => 'Speichern','translation_domain' => 'form'])
        ;
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Active::class,
        ]);
    }
}
