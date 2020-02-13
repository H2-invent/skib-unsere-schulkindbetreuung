<?php
/**
 * Created by PhpStorm.
 * User: Emanuel
 * Date: 17.09.2019
 * Time: 20:29
 */

namespace App\Form\Type;


use App\Entity\Stammdaten;
use Symfony\Component\Form\AbstractType;

use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class LoerrachEltern extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('email', EmailType::class, ['label' => 'E-Mail', 'translation_domain' => 'form'])
            ->add('vorname', TextType::class, ['label' => 'Vorname', 'translation_domain' => 'form'])
            ->add('name', TextType::class, ['label' => 'Nachname', 'translation_domain' => 'form'])
            ->add('strasse', TextType::class, ['label' => 'Straße Nr.', 'translation_domain' => 'form'])
            ->add('adresszusatz', TextType::class, ['required' => false, 'label' => 'Adresszusatz', 'translation_domain' => 'form'])
            ->add('plz', TextType::class, ['label' => 'Postleitzahl', 'translation_domain' => 'form'])
            ->add('stadt', TextType::class, ['label' => 'Stadt', 'translation_domain' => 'form', 'help' => 'Das ist eine Hilfe für diese Frage im Form'])
            ->add('einkommen', ChoiceType::class, [
                'choices' => $options['einkommen'], 'label' => 'Brutto Haushaltseinkommen pro Monat', 'translation_domain' => 'form'])
            ->add('beruflicheSituation', ChoiceType::class, ['choices' => $options['beruflicheSituation'], 'required' => true, 'label' => 'Berufliche Situation des Erziehungsberechtigten', 'translation_domain' => 'form'])
            ->add('kinderImKiga', CheckboxType::class, ['required' => false, 'label' => 'Ich habe mindestens ein weiteres Kind in einer kostenpflichtigen öffentlichen Kindergarteneinrichtung', 'translation_domain' => 'form'])
            ->add('alleinerziehend', CheckboxType::class, ['required' => false, 'label' => 'Ich bin alleinerziehend', 'translation_domain' => 'form'])
            ->add('notfallName', TextType::class, ['required' => true, 'label' => 'Name des Notfallkontakts', 'translation_domain' => 'form'])
            ->add('notfallkontakt', TextType::class, ['required' => true, 'label' => 'Notfalltelefonnummer', 'translation_domain' => 'form'])
           ->add('abholberechtigter', TextareaType::class, ['required' => false, 'label' => 'Weitere abholberechtigte Personen', 'translation_domain' => 'form', 'attr' => ['rows' => 6]])
            ->add('gdpr', CheckboxType::class, ['required' => true, 'label' => 'Ich bin damit einverstanden, dass meine Daten und die Daten meiner Kinder elektronisch verarbeitet werden und an die betreuende Organisation weitergegeben werden.', 'translation_domain' => 'form'])
            ->add('submit', SubmitType::class, ['attr' => array('class' => 'btn btn-outline-primary'), 'label' => 'weiter', 'translation_domain' => 'form']);
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Stammdaten::class,
            'einkommen' => array(),
            'beruflicheSituation' => array(),

        ]);

    }
}

