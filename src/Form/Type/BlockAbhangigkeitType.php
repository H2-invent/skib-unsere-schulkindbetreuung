<?php
/**
 * Created by PhpStorm.
 * User: Emanuel
 * Date: 17.09.2019
 * Time: 20:29
 */

namespace App\Form\Type;

use App\Entity\Active;

use App\Entity\Schule;
use App\Entity\Tags;
use App\Entity\Zeitblock;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;

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
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichFileType;
use Vich\UploaderBundle\Form\Type\VichImageType;

class BlockAbhangigkeitType extends AbstractType
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {



        $builder
            ->add('vorganger', EntityType::class, [
                'class' => Zeitblock::class,
                'choice_label' => function (Zeitblock $zeitblock) {
                    return $zeitblock->getVon()->format('H:i') . '-' . $zeitblock->getBis()->format('H:i');
                },
                'placeholder' => 'Ganztag oder Halbtag muss gewählt sein',
                'label' => 'Muss auch gewählt sein (strg halten für Mehrfachauswahl)',
                'translation_domain' => 'form',
                'multiple' => true,
                'expanded' => false,
                'choices' => $options['blocks'],
                'group_by' => function (Zeitblock $zeitblock, $key, $value) {
                    return $zeitblock->getWochentagString();
                },
            ]);



    }



    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Zeitblock::class,
            'anzahlPreise' => 1,
            'blocks'=>array()
        ]);
        $resolver->setAllowedTypes('anzahlPreise', 'integer');
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_zeitblock';
    }
}
