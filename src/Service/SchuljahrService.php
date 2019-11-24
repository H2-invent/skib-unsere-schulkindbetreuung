<?php

namespace App\Service;

use App\Entity\Active;
use App\Entity\Organisation;
use App\Entity\Stadt;

use App\Entity\Stammdaten;
use Beelab\Recaptcha2Bundle\Form\Type\RecaptchaType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Validator\ValidatorInterface;


// <- Add this

class SchuljahrService
{
    private $validator;
    private $formFactory;
    private $em;
    private $params;
    private $user;
   public function __construct(Security $security,ValidatorInterface $validator,FormFactoryInterface $formFactory,EntityManagerInterface $entityManager,ParameterBagInterface $params)
   {
        $this->validator = $validator;
       $this->formFactory = $formFactory;
       $this->em = $entityManager;
       $this->params = $params;
       $this->user = $security;
   }

    public
    function getSchuljahr(Stadt $stadt)
    {
        if ($this->user->getUser() && $this->user->getUser()->hasRole('ROLE_ORG_CHILD_CHANGE')) {
            return $this->em->getRepository(Active::class)->findSchuljahrFromCity($stadt);
        } else {
            return $this->em->getRepository(Active::class)->findActiveSchuljahrFromCity($stadt);
        }
    }

}
