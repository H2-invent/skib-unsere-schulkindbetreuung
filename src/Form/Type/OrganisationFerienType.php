<?php
/**
 * Created by PhpStorm.
 * User: Emanuel
 * Date: 17.09.2019
 * Time: 20:29
 */
namespace App\Form\Type;

use App\Entity\Organisation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;

class OrganisationFerienType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('stornoGebuehr', NumberType::class,['required'=>true,'label'=>'Stornogebühr für das Ferienprogramm','translation_domain' => 'form'])
            ->add('ansprechpartnerFerien', TextType::class,['required'=>true,'label'=>'Ansprechpartner Ferien','translation_domain' => 'form'])
            ->add('ansprechpartnerFerienEmail', TextType::class,['required'=>true,'label'=>'Ansprechpartner Telefon','translation_domain' => 'form'])
            ->add('ansprechpartnerFerienPhone', TextType::class,['required'=>true,'label'=>'Ansprechpartner Email','translation_domain' => 'form'])
            ->add('ferienRegulation', TextareaType::class,['label'=>'Regelungen Ferienprogram (Markdown)','translation_domain' => 'form'])
            ->add('submit', SubmitType::class, ['label' => 'Speichern','translation_domain' => 'form'])
        ;
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Organisation::class,
        ]);
    }
}
