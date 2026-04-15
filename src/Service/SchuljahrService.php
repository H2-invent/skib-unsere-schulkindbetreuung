<?php

namespace App\Service;

use App\Entity\Active;
use App\Entity\Stadt;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;

class SchuljahrService
{
    public const SESSION_KEY_SCHULJAHR = 'schuljahr_to_add';

    public function __construct(
        private Security $user,
        private EntityManagerInterface $em,
        private RequestStack $requestStack,
    ) {
    }

    public function getSchuljahr(Stadt $stadt): ?Active
    {
        if ($this->requestStack->getSession()->get(self::SESSION_KEY_SCHULJAHR)) {
            $schuljahr = $this->em->getRepository(Active::class)->find($this->requestStack->getSession()->get(self::SESSION_KEY_SCHULJAHR));

            return $schuljahr;
        }

        if ($this->user->getUser() && $this->user->getUser()->hasRole('ROLE_ORG_CHILD_CHANGE')) {
            return $this->em->getRepository(Active::class)->findSchuljahrFromCity($stadt, new \DateTime());
        }

        return $this->em->getRepository(Active::class)->findAnmeldeSchuljahrFromCity($stadt);
    }
}
