<?php

namespace App\Service;

use App\Entity\Active;
use App\Entity\Organisation;
use App\Entity\Stadt;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Validator\ValidatorInterface;


class SchuljahrService
{

    public const SESSION_KEY_SCHULJAHR = 'schuljahr_to_add';

    private $em;
    private $user;

    public function __construct(Security $security, EntityManagerInterface $entityManager, private RequestStack $requestStack)
    {


        $this->em = $entityManager;

        $this->user = $security;
    }

    public function getSchuljahr(Stadt $stadt): ?Active
    {
        if ($this->requestStack->getSession()->get(self::SESSION_KEY_SCHULJAHR)) {
            $schuljahr = $this->em->getRepository(Active::class)->find($this->requestStack->getSession()->get(self::SESSION_KEY_SCHULJAHR));
            return $schuljahr;
        }

        if ($this->user->getUser() && $this->user->getUser()->hasRole('ROLE_ORG_CHILD_CHANGE')) {
            return $this->em->getRepository(Active::class)->findSchuljahrFromCity($stadt, new \DateTime());
        } else {
            return $this->em->getRepository(Active::class)->findAnmeldeSchuljahrFromCity($stadt);
        }
    }

}
