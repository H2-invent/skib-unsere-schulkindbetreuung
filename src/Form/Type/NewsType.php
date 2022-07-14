<?php
/**
 * Created by PhpStorm.
 * User: Emanuel
 * Date: 17.09.2019
 * Time: 20:29
 */

namespace App\Form\Type;

use App\Entity\Active;

use App\Entity\News;
use App\Entity\Organisation;
use App\Entity\Schule;
use Doctrine\DBAL\Types\BooleanType;
use phpDocumentor\Reflection\Types\Boolean;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichFileType;
use Vich\UploaderBundle\Form\Type\VichImageType;

class NewsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, array('label' => 'Titel', 'required' => true, 'translation_domain' => 'form'))
            ->add('message', TextareaType::class, array('attr' => ['rows' => 6, 'class' => 'onlineEditor'], 'label' => 'Nachricht', 'required' => true, 'translation_domain' => 'form'))
            ->add('activ', CheckboxType::class, array('required' => false, 'label' => 'Neuigkeit auf der Startseite sichtbar', 'translation_domain' => 'form'))
//            ->add('sendToAngemeldet', CheckboxType::class,array('required'=>false,'label'=>'Ranzenpost an Eltern schicken, die ihr Kinder nur bei einem Kontingent angemeldet haben','translation_domain' => 'form'))
//             ->add('sendToGebucht', CheckboxType::class,array('required'=>false,'label'=>'Ranzenpost an Eltern schicken, die für ihre  Kinder bereits eine Buchungsbestätigung erhalten haben','translation_domain' => 'form'))
            ->add('schule', EntityType::class, [
                'choice_label' => 'name',
                'class' => Schule::class,
                'choices' => $options['schulen'],
                'label' => 'Name der Schule für den Versand der Nachricht (Strg drücken für Mehrfachauswahl)',
                'translation_domain' => 'form',
                'multiple' => true,
                'required' => false
            ])
            ->add('schuljahre', EntityType::class, [
                'choice_label' => function (Active $active) {
                    return $active->getVon()->format('d.m.Y') . '-' . $active->getBis()->format('d.m.Y');
                },
                'class' => Active::class,
                'choices' => $options['schuljahre'],
                'label' => 'Schuljahr auswählen an welche die E-Mail versandt werden soll(Strg drücken für Mehrfachauswahl)',
                'translation_domain' => 'form',
                'multiple' => true,
                'required' => false
            ])
//            ->add('attachmentFile', VichFileType::class, [
//                'required' => false,
//                'allow_delete' => true,
//                'delete_label' => 'Löschen',
//                'label'=>'Einen E-Mail-Anhang anhängen (nur bei E-Mails)',
//                'translation_domain' => 'form'
//            ])


            ->add('save', SubmitType::class, ['label' => 'Speichern', 'translation_domain' => 'form']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => News::class,
            'schulen' => array(),
            'schuljahre' => array(),
        ]);
    }
}
