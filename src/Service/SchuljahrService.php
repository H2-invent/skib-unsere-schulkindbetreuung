<?php

namespace App\Service;

use App\Entity\Active;
use App\Entity\Organisation;
use App\Entity\Stadt;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Validator\ValidatorInterface;


// <- Add this

class SchuljahrService
{


    private $em;
    private $params;
    private $user;
   public function __construct(Security $security,ValidatorInterface $validator,FormFactoryInterface $formFactory,EntityManagerInterface $entityManager,ParameterBagInterface $params)
   {


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
