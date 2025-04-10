<?php
/**
 * Created by PhpStorm.
 * User: Emanuel
 * Date: 17.09.2019
 * Time: 20:29
 */
namespace App\Form\Type;



use App\Entity\Kind;

use App\Entity\KindFerienblock;
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

class FerienblockKindQuestion extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {


        $builder
            ->add('vorname', TextType::class,['label'=>'Vorname','translation_domain' => 'form'])
            ->add('nachname', TextType::class,['label'=>'Name','translation_domain' => 'form'])
            ->add('klasse', ChoiceType::class, [
                'choices'  => [
                    'Klasse 1' => 1,
                    'Klasse 2' => 2,
                    'Klasse 3' => 3,
                    'Klasse 4' => 4,
                    'Klasse 5' => 5,
                    'Klasse 6' => 6,
                ],'label'=>'Jahrgangsstufe','translation_domain' => 'form'])
            ->add('art', ChoiceType::class, [
                'choices'  => [
                    'Ganztag' => 1,
                    'Halbtag' => 2,
                ],'label'=>'Schulform','translation_domain' => 'form'])
            ->add('geburtstag', BirthdayType::class,['years'=>range($today-20,$today,1),'label'=>'Geburtstag','translation_domain' => 'form'])
            ->add('allergie', TextType::class,['required'=>false,'label'=>'Mein Kind hat folgende Allergien','translation_domain' => 'form'])
            ->add('medikamente', TextType::class,['required'=>false,'label'=>'Mein Kind benötig folgende Medikamente','translation_domain' => 'form'])
            ->add('gluten', CheckboxType::class,['required'=>false,'label'=>'Mein Kind ist glutenintolerant','translation_domain' => 'form'])
            ->add('laktose', CheckboxType::class,['required'=>false,'label'=>'Mein Kind ist laktoseintolerant','translation_domain' => 'form'])
            ->add('schweinefleisch', CheckboxType::class,['required'=>false,'label'=>'Mein Kind isst kein Schweinefleich','translation_domain' => 'form'])
            ->add('vegetarisch', CheckboxType::class,['required'=>false,'label'=>'Mein Kind ernährt sich vegetarisch','translation_domain' => 'form'])
            ->add('alleineHause', CheckboxType::class,['required'=>false,'label'=>'Mein Kind darf nach Ende der gebuchten Betreuung alleine nach Hause','translation_domain' => 'form'])
            ->add('ausfluege', CheckboxType::class,['required'=>false,'label'=>'Mein Kind darf an Ausflügen teilnehmen','translation_domain' => 'form'])
            ->add('sonnencreme', CheckboxType::class,['required'=>false,'label'=>'Mein Kind darf im Sommer mit handelsüblicher Sonnencreme eingecremt werden','translation_domain' => 'form'])
            ->add('fotos', CheckboxType::class,['required'=>false,'label'=>'Fotos, auf welchen mein Kind zu sehen ist, dürfen sowohl in der öffentlichen Presse veröffentlicht, als auch für die Öffentlichkeitsarbeit der betreuenden Organisationen genutzt werden.','translation_domain' => 'form'])
            ->add('bemerkung', TextareaType::class,['required'=>false,'label'=>'Bemerkung','translation_domain' => 'form','attr'=>['rows'=>6]]);
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => KindFerienblock::class,
        ]);

    }
}
