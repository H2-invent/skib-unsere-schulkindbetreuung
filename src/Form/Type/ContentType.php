<?php
/**
 * Created by PhpStorm.
 * User: Emanuel
 * Date: 17.09.2019
 * Time: 20:29
 */
namespace App\Form\Type;

use App\Entity\Active;

use App\Entity\Content;
use App\Entity\News;
use App\Entity\Organisation;
use App\Entity\Schule;
use Doctrine\DBAL\Types\BooleanType;
use phpDocumentor\Reflection\Types\Boolean;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $content = $options['data'];
        if($content->getTranslations()->isEmpty()){
            $content->translate('de')->setTitle('');
            $content->translate('en')->setTitle('');
            $content->translate('fr')->setTitle('');
            $content->translate('de')->setContent('');
            $content->translate('en')->setContent('');
            $content->translate('fr')->setContent('');
            foreach ($content->getNewTranslations() as $newTranslation) {
                if (!$content->getTranslations()->contains($newTranslation) && !$content->getNewTranslations()->isEmpty()) {
                    $content->addTranslation($newTranslation);
                    $content->getNewTranslations()->removeElement($newTranslation);
                }
            }
        }
        $builder
            ->add('slug', TextType::class,array('label'=>'Slug','required'=>true,'translation_domain' => 'form'))
            ->add('titleDE', TextType::class, ['required'=>false,'label'=>'Title Deutsch ','translation_domain' => 'form', 'property_path' => 'translations[de].title', ])
            ->add('titleEN', TextType::class, ['required'=>false,'label'=>'Title Englisch ','translation_domain' => 'form','property_path' => 'translations[en].title', ])
            ->add('titleFR', TextType::class, ['required'=>false,'label'=>'Title Französisch ','translation_domain' => 'form','property_path' => 'translations[fr].title', ])
            ->add('contentDE', TextareaType::class, ['attr'=>['rows'=>6,'class'=>'onlineEditor'],'required'=>false,'label'=>'Content Deutsch (Markdown)','translation_domain' => 'form', 'property_path' => 'translations[de].content', ])
            ->add('contentEN', TextareaType::class, ['attr'=>['rows'=>6,'class'=>'onlineEditor'],'required'=>false,'label'=>'Content Englisch (Markdown)','translation_domain' => 'form','property_path' => 'translations[en].content', ])
            ->add('contentFR', TextareaType::class, ['attr'=>['rows'=>6,'class'=>'onlineEditor'],'required'=>false,'label'=>'Content Französisch (Markdown)','translation_domain' => 'form','property_path' => 'translations[fr].content', ])
            ->add('metaDE', TextType::class, ['required'=>false,'label'=>'Meta Deutsch ','translation_domain' => 'form', 'property_path' => 'translations[de].meta', ])
            ->add('metaEN', TextType::class, ['required'=>false,'label'=>'Meta Englisch ','translation_domain' => 'form','property_path' => 'translations[en].meta', ])
            ->add('metaFR', TextType::class, ['required'=>false,'label'=>'Meta Französisch','translation_domain' => 'form','property_path' => 'translations[fr].meta', ])

            ->add('icon', TextType::class,array('label'=>'Icon (Fontawesome)','required'=>true,'translation_domain' => 'form'))

            ->add('activ', CheckboxType::class,array('label'=>'Content öffentlich','translation_domain' => 'form'))
            ->add('reihenfolge', NumberType::class,array('label'=>'Reihenfolge','translation_domain' => 'form'))

            ->add('save', SubmitType::class, ['label' => 'Speichern','translation_domain' => 'form'])
        ;
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Content::class,
        ]);
    }
}
