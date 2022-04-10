<?php

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class UserManager implements UserManagerInterface
{
    private $em;
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    public function createUser()
    {
       $user = new User();
       return $user;
    }

    public function deleteUser(User $user)
    {
        // TODO: Implement deleteUser() method.
    }

    public function findUserBy(array $criteria)
    {
        return $this->em->getRepository(User::class)->findOneBy($criteria);
    }

    public function findUserByUsername($username)
    {
        // TODO: Implement findUserByUsername() method.
    }

    public function findUserByEmail($email)
    {
        // TODO: Implement findUserByEmail() method.
    }

    public function findUserByUsernameOrEmail($usernameOrEmail)
    {
        // TODO: Implement findUserByUsernameOrEmail() method.
    }

    public function findUserByConfirmationToken($token)
    {
        // TODO: Implement findUserByConfirmationToken() method.
    }

    public function findUsers()
    {
        return $this->em->getRepository(User::class)->findAll();
    }

    public function getClass()
    {
        // TODO: Implement getClass() method.
    }

    public function reloadUser(User $user)
    {
        // TODO: Implement reloadUser() method.
    }

    public function updateUser(User $user)
    {
        $this->em->persist($user);
        $this->em->flush();
    }

    public function updateCanonicalFields(User $user)
    {
        // TODO: Implement updateCanonicalFields() method.
    }

    public function updatePassword(User $user)
    {
        // TODO: Implement updatePassword() method.
    }
}