<?php
/**
 * Created by PhpStorm.
 * User: Emanuel
 * Date: 17.09.2019
 * Time: 20:29
 */
namespace App\Form\Type;

use App\Entity\Active;

use App\Entity\Sepa;

use Symfony\Component\Form\AbstractType;

use Symfony\Component\Form\Extension\Core\Type\DateType;

use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class SepaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {


        $today = new \DateTime();
        $firstDay = clone $today;
        $firstDay->modify('first day of this month');
        $lastDay = clone $today;
        $lastDay->modify('last day of this month');
        $einzugsdatum = $today->modify('+6 days');
        $einzugsdatum = $einzugsdatum->modify('next monday');
        $builder
            ->add('von', DateType::class,array('data'=>$firstDay,'label'=>'Stichtag ist der 1. des Monats für die Abrechnung','required'=>true,'translation_domain' => 'form'))
            ->add('einzugsDatum', DateType::class,array('widget'=>'single_text', 'data'=>$einzugsdatum,'label'=>'Tag des Lastschrifteinzugs','required'=>true,'translation_domain' => 'form'))
            ->add('save', SubmitType::class, ['label' => 'Speichern','translation_domain' => 'form'])
        ;
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Sepa::class,
        ]);

    }
}
