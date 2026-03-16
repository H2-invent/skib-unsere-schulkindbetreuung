<?php

namespace App\Form\Type;

use App\Entity\Organisation;
use App\Entity\Stadt;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdminUserType extends AbstractType
{
    private $availableRoles = [
        'ROLE_CITY_DASHBOARD' => 'ROLE_CITY_DASHBOARD',
        'ROLE_CITY_SCHOOL' => 'ROLE_CITY_SCHOOL',
        'ROLE_CITY_REPORT' => 'ROLE_CITY_REPORT',
        'ROLE_CITY_NEWS' => 'ROLE_CITY_NEWS',
        'ROLE_ORG_REPORT' => 'ROLE_ORG_REPORT',
        'ROLE_ORG_CHILD_CHANGE' => 'ROLE_ORG_CHILD_CHANGE',
        'ROLE_ORG_CHILD_EMAIL_CHANGE' => 'ROLE_ORG_CHILD_EMAIL_CHANGE',
        'ROLE_ORG_CHILD_SCHOOLYEAR_CHANGE' => 'ROLE_ORG_CHILD_SCHOOLYEAR_CHANGE',
        'ROLE_ORG_CHILD_SHOW' => 'ROLE_ORG_CHILD_SHOW',
        'ROLE_ORG_CHILD_DOCUMENT_DELETE' => 'ROLE_ORG_CHILD_DOCUMENT_DELETE',
        'ROLE_ORG_ACCOUNTING' => 'ROLE_ORG_ACCOUNTING',
        'ROLE_ORG_BLOCK_MANAGEMENT' => 'ROLE_ORG_BLOCK_MANAGEMENT',
        'ROLE_ORG_BLOCK_DELETE' => 'ROLE_ORG_BLOCK_DELETE',
        'ROLE_ORG_SHOOL' => 'ROLE_ORG_SHOOL',
        'ROLE_ORG_NEWS' => 'ROLE_ORG_NEWS',
        'ROLE_ORG_CHILD_DELETE' => 'ROLE_ORG_CHILD_DELETE',
        'ROLE_ORG_ACCEPT_CHILD' => 'ROLE_ORG_ACCEPT_CHILD',
        'ROLE_ORG_BLOCK_DEACTIVATE' => 'ROLE_ORG_BLOCK_DEACTIVATE',
        'ROLE_ORG_SEE_PRICE' => 'ROLE_ORG_SEE_PRICE',
        'ROLE_ORG_VIEW_NOTICE' => 'ROLE_ORG_VIEW_NOTICE',
        'ROLE_ORG_EDIT_NOTICE' => 'ROLE_ORG_EDIT_NOTICE',
        'ROLE_ORG_FERIEN_EDITOR' => 'ROLE_ORG_FERIEN_EDITOR',
        'ROLE_ORG_FERIEN_REPORT' => 'ROLE_ORG_FERIEN_REPORT',
        'ROLE_ORG_FERIEN_ORDERS' => 'ROLE_ORG_FERIEN_ORDERS',
        'ROLE_ORG_FERIEN_CHECKIN' => 'ROLE_ORG_FERIEN_CHECKIN',
        'ROLE_ORG_FERIEN_ADMIN' => 'ROLE_ORG_FERIEN_ADMIN',
        'ROLE_ORG_FERIEN_STORNO' => 'ROLE_ORG_FERIEN_STORNO',
        'ROLE_ORG_CHECKIN_SHOW' => 'ROLE_ORG_CHECKIN_SHOW',
        'ROLE_ORG_KVJS' => 'ROLE_ORG_KVJS'
    ];

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('vorname')
            ->add('nachname')
            ->add('email')
            ->add('birthday', BirthdayType::class, [
                'widget' => 'single_text',
                'required' => false,
                'label' => 'Geburtstag',
            ])
            ->add('roles', ChoiceType::class, [
                'choices' => $this->availableRoles,
                'multiple' => true,
                'expanded' => true,
                'label' => 'Roles',
            ])
            ->add('stadt', EntityType::class, [
                'class' => Stadt::class,
                'choice_label' => 'name',
            ])
            ->add('organisation', EntityType::class, [
                'class' => Organisation::class,
                'choice_label' => 'name',
            ])
            ->add('enabled')
            ->add('save', SubmitType::class, ['label' => 'Speichern'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
