<?php
/**
 * Created by PhpStorm.
 * User: Emanuel
 * Date: 17.09.2019
 * Time: 20:29
 */
namespace App\Form\Type;

use App\Entity\Active;

use App\Entity\Zeitblock;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
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

class BlockType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder

            ->add('von', TimeType::class,array('label'=>'Betreuungsbeginn','required'=>true,'translation_domain' => 'form'))
            ->add('bis', TimeType::class,array('label'=>'Betreuungsende','required'=>true,'translation_domain' => 'form'));
            for($i = 0; $i<$options['anzahlPreise'];$i++){
              $builder->add('preis', NumberType::class,array('label'=>'Preis','required'=>true,'translation_domain' => 'form'));
            }
            $builder->add('save', SubmitType::class, ['label' => 'Speichern','translation_domain' => 'form'])
        ;
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Zeitblock::class,
             'anzahlPreise' => 1,
        ]);
        $resolver->setAllowedTypes('anzahlPreise', 'integer');
    }
}
