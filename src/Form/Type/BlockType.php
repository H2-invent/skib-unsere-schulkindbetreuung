<?php

/**
 * Created by PhpStorm.
 * User: Emanuel
 * Date: 17.09.2019
 * Time: 20:29.
 */

namespace App\Form\Type;

use A2lix\TranslationFormBundle\Form\Type\TranslationsType;
use App\Entity\Zeitblock;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BlockType extends AbstractType
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('von', TimeType::class, ['widget' => 'single_text', 'label' => 'Betreuungsbeginn', 'required' => true, 'translation_domain' => 'form', 'attr' => ['cl']])
            ->add('bis', TimeType::class, ['widget' => 'single_text', 'label' => 'Betreuungsende', 'required' => true, 'translation_domain' => 'form'])
            ->add('min', NumberType::class, ['label' => 'Mindestanzahl an Kindern (Leerlassen wenn keine Begrenzung)', 'required' => false, 'translation_domain' => 'form'])
            ->add('max', NumberType::class, ['label' => 'Maximalanzahl an Kindern (Leerlassen wenn keine Begrenzung)', 'required' => false, 'translation_domain' => 'form'])
            ->add('ganztag', ChoiceType::class, [
                'choices' => [
                    'Ganztagsbetreuung' => 1,
                    'Halbtagsbetreuung' => 2,
                    'Mittagessen' => 0,
                ], 'label' => 'Art der Betreuung', 'translation_domain' => 'form'])
            ->add('preise', CollectionType::class, [
                'entry_type' => NumberType::class,
                'entry_options' => ['label' => 'Preis', 'required' => true, 'translation_domain' => 'form'],
            ])
            ->add('hidePrice', CheckboxType::class, ['required' => false, 'label' => 'Preis in der Anzeige verstecken', 'translation_domain' => 'form'])
            ->add('direktbuchungDeaktiviert', CheckboxType::class, [
                'required' => false,
                'label' => 'Direkte Auswahl deaktivieren (nur über Abhängigkeiten auswählbar)',
                'translation_domain' => 'form',
            ])

            ->add('translations', TranslationsType::class, [
                'fields' => [
                    'extraText' => [
                        'attr' => ['rows' => 6, 'class' => 'onlineEditor'],
                        'label' => 'Text welcher bei einem Zeitblock angezeigt werden soll',
                        'translation_domain' => 'form',
                    ],
                    'blockbezeichnung' => [
                        'attr' => ['rows' => 1, 'class' => 'onlineEditor'],
                        'label' => 'Blockbezeichnung der Elternkommunikation',
                        'translation_domain' => 'form',
                    ],
                ],
            ]
            )

            ->add('save', SubmitType::class, ['label' => 'Speichern', 'translation_domain' => 'form']);
    }

    protected function addElements(FormInterface $form, Zeitblock $zeitblock)
    {
        $vorganger = [];
        if ($zeitblock->getGanztag()) {
            $vorganger = $this->em->getRepository(Zeitblock::class)->findBy(
                ['schule' => $zeitblock->getSchule(),
                    'active' => $zeitblock->getActive(),
                    'ganztag' => $zeitblock->getGanztag()]);
        }

        $form->add('vorganger', EntityType::class, [
            'class' => Zeitblock::class,
            'choice_label' => fn (Zeitblock $zeitblock) => $zeitblock->getVon()->format('H:i') . '-' . $zeitblock->getBis()->format('H:i'),
            'placeholder' => 'Ganztag oder Halbtag muss gewählt sein',
            'label' => 'Muss auch gewählt sein (strg halten für Mehrfachauswahl)',
            'translation_domain' => 'form',
            'multiple' => true,
            'expanded' => false,
            'choices' => $vorganger,
            'group_by' => fn (Zeitblock $zeitblock, $key, $value) => $zeitblock->getWochentagString(),
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Zeitblock::class,
            'anzahlPreise' => 1,
        ]);
        $resolver->setAllowedTypes('anzahlPreise', 'integer');
    }

    public function getBlockPrefix()
    {
        return 'appbundle_zeitblock';
    }
}
