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


class SchuljahrService
{


    private $em;
    private $user;

    public function __construct(Security $security, EntityManagerInterface $entityManager)
    {


        $this->em = $entityManager;

        $this->user = $security;
    }

    public function getSchuljahr(Stadt $stadt): ?Active
    {
        if ($this->user->getUser() && $this->user->getUser()->hasRole('ROLE_ORG_CHILD_CHANGE')) {
            return $this->em->getRepository(Active::class)->findSchuljahrFromCity($stadt, new \DateTime());
        } else {
            return $this->em->getRepository(Active::class)->findAnmeldeSchuljahrFromCity($stadt);
        }
    }

}
