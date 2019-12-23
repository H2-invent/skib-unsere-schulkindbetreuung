<?php
/**
 * Created by PhpStorm.
 * User: Emanuel
 * Date: 17.09.2019
 * Time: 20:29
 */

namespace App\Form\Type;

use App\Entity\Ferienblock;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FerienBlockType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $ferienblock = $options['data'];
        if ($ferienblock->getTranslations()->isEmpty()) {
            $ferienblock->translate('de')->setTitel('');
            $ferienblock->translate('en')->setTitel('');
            $ferienblock->translate('fr')->setTitel('');
            $ferienblock->translate('de')->setInfoText('');
            $ferienblock->translate('en')->setInfoText('');
            $ferienblock->translate('fr')->setInfoText('');

            foreach ($ferienblock->getNewTranslations() as $newTranslation) {
                if (!$ferienblock->getTranslations()->contains($newTranslation) && !$ferienblock->getNewTranslations()->isEmpty()) {
                    $ferienblock->addTranslation($newTranslation);
                    $ferienblock->getNewTranslations()->removeElement($newTranslation);
                }
            }
        }

        $builder
            ->add('titelDE', TextType::class, ['label' => 'Titel Deutsch', 'translation_domain' => 'form', 'property_path' => 'translations[de].titel',])
            ->add('titelEN', TextType::class, ['label' => 'Titel Englisch', 'translation_domain' => 'form', 'property_path' => 'translations[en].titel',])
            ->add('titelFR', TextType::class, ['label' => 'Titel Französisch', 'translation_domain' => 'form', 'property_path' => 'translations[fr].titel',])
            ->add('infoTextDE', TextareaType::class, ['attr' => ['rows' => 6], 'label' => 'Info Text Deutsch DE', 'translation_domain' => 'form', 'property_path' => 'translations[de].infoText',])
            ->add('infoTextEN', TextareaType::class, ['attr' => ['rows' => 6], 'label' => 'Info Text Englisch', 'translation_domain' => 'form', 'property_path' => 'translations[en].infoText',])
            ->add('infoTextFR', TextareaType::class, ['attr' => ['rows' => 6], 'label' => 'Info Text Französisch', 'translation_domain' => 'form', 'property_path' => 'translations[fr].infoText',])
            ->add('ort', TextareaType::class, ['label' => 'Stadt', 'translation_domain' => 'form'])
            //->add('warteliste', CheckboxType::class, array('required' => false, 'label' => 'Ferienblock mit Warteliste', 'translation_domain' => 'form'))
            //->add('modeMaximal', CheckboxType::class, array('required' => false, 'label' => 'Manuelle Bestätigung der Teilnehmer', 'translation_domain' => 'form'))
            ->add('minAlter', IntegerType::class, array('required' => true, 'label' => 'Mindest Alter', 'translation_domain' => 'form'))
            ->add('maxAlter', IntegerType::class, array('required' => false, 'label' => 'Maximum Alter', 'translation_domain' => 'form'))
            ->add('startDate', DateType::class, array('widget' => 'single_text', 'label' => 'Datum Start', 'translation_domain' => 'form', 'attr' => array('class' => 'start date-hkjdshfsh')))
            ->add('endDate', DateType::class, ['widget' => 'single_text', 'label' => 'Datum Ende', 'translation_domain' => 'form', 'attr' => array('class' => 'end date-hkjdshfsh')])
            ->add('startTime', TimeType::class, ['widget' => 'single_text', 'label' => 'Uhrzeit Anfang', 'translation_domain' => 'form'])
            ->add('endTime', TimeType::class, ['widget' => 'single_text', 'label' => 'Uhrzeit Ende', 'translation_domain' => 'form'])
            ->add('startVerkauf', DateType::class, array('widget' => 'single_text', 'label' => 'Start Vorverkauf', 'translation_domain' => 'form'))
            ->add('endVerkauf', DateType::class, array('widget' => 'single_text', 'label' => 'End Vorverkauf', 'translation_domain' => 'form'))
            ->add('maxAnzahl', IntegerType::class, array('required' => false, 'label' => 'Maximum Anzahl', 'translation_domain' => 'form'))
            ->add('anzahlPreise', IntegerType::class, array('required' => true, 'label' => 'Anzahl Preise', 'translation_domain' => 'form'))
            ->add('amountVoucher', IntegerType::class, array('required' => true, 'label' => 'Anzahl Gutscheine', 'translation_domain' => 'form'))

            ->add('save', SubmitType::class, ['label' => 'Speichern', 'translation_domain' => 'form']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Ferienblock::class,
        ]);
    }
}