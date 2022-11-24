<?php
/**
 * Created by PhpStorm.
 * User: Emanuel
 * Date: 17.09.2019
 * Time: 20:29
 */

namespace App\Form\Type;


use App\Entity\Active;
use App\Entity\Kind;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LoerrachKind extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {


        $today = intval((new \DateTime())->format('Y'));
        $stadt = $options['data']->getSchule()->getStadt();

        if (isset($options['schuljahr'])) {
            $builder->add('startDate', DateType::class, ['attr' => array('class' => 'pickadate', 'data-min' => $options['schuljahr']->getVon()->format('d.m.Y'), 'data-max' => $options['schuljahr']->getBis()->format('d.m.Y')), 'widget' => 'single_text', 'label' => 'Startdatum', 'translation_domain' => 'form']);
        }

        $builder->add('vorname', TextType::class, ['label' => 'Vorname', 'translation_domain' => 'form'])
            ->add('nachname', TextType::class, ['label' => 'Nachname', 'translation_domain' => 'form'])
            ->add('klasse', ChoiceType::class, [
                'choices' => array_flip($stadt->translate()->getSettingsSkibShoolyearNamingArray()), 'label' => 'Jahrgangsstufe zu Betreuungsbeginn', 'translation_domain' => 'form'])
            ->add('art', ChoiceType::class, [
                'choices' => [
                    'Ganztag' => 1,
                    'Halbtag' => 2,
                ], 'label' => 'Schulform', 'translation_domain' => 'form'])
            ->add('geburtstag', BirthdayType::class, ['attr' => array('class' => 'pickadate'), 'widget' => 'single_text', 'years' => range($today - 20, $today, 1), 'label' => 'Geburtstag', 'translation_domain' => 'form'])
            ->add('masernImpfung', CheckboxType::class, array('label' => 'Mein Kind ist gegen Masern geimpft / bereits immun'))
            ->add('allergie', TextType::class, ['required' => false, 'label' => 'Mein Kind hat folgende Allergien', 'translation_domain' => 'form'])
            ->add('medikamente', TextType::class, ['required' => false, 'label' => 'Mein Kind benötigt folgende Medikamente', 'translation_domain' => 'form'])
            ->add('gluten', CheckboxType::class, ['required' => false, 'label' => 'Mein Kind ist glutenintolerant', 'translation_domain' => 'form'])
            ->add('laktose', CheckboxType::class, ['required' => false, 'label' => 'Mein Kind ist laktoseintolerant', 'translation_domain' => 'form'])
            ->add('schweinefleisch', CheckboxType::class, ['required' => false, 'label' => 'Mein Kind isst kein Schweinefleich', 'translation_domain' => 'form'])
            ->add('vegetarisch', CheckboxType::class, ['required' => false, 'label' => 'Mein Kind ernährt sich vegetarisch', 'translation_domain' => 'form'])
            ->add('alleineHause', CheckboxType::class, ['required' => false, 'label' => 'Mein Kind darf nach Ende der gebuchten Betreuung alleine nach Hause', 'translation_domain' => 'form'])
            ->add('ausfluege', CheckboxType::class, ['required' => false, 'label' => 'Mein Kind darf an Ausflügen teilnehmen', 'translation_domain' => 'form'])
            ->add('sonnencreme', CheckboxType::class, ['required' => false, 'label' => 'Mein Kind darf im Sommer mit handelsüblicher Sonnencreme eingecremt werden', 'translation_domain' => 'form'])
            ->add('fotos', CheckboxType::class, ['required' => false, 'label' => 'Fotos, auf welchen mein Kind zu sehen ist, dürfen sowohl in der öffentlichen Presse veröffentlicht, als auch für die Öffentlichkeitsarbeit der betreuenden Organisationen genutzt werden.', 'translation_domain' => 'form'])
            ->add('bemerkung', TextareaType::class, ['required' => false, 'label' => 'Bemerkung', 'translation_domain' => 'form', 'attr' => ['rows' => 6]]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Kind::class,
            'schuljahr'=>Active::class
        ]);

    }
}
