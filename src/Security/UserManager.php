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
       $user->setCreatedAt(new \DateTime());
       $user->setEnabled(true);
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
        return $this->em->getRepository(User::class)->findOneBy(array('uuid'=>$username));

    }

    public function findUserByEmail($email)
    {
        return $this->em->getRepository(User::class)->findOneBy(array('email'=>$email));

    }

    public function findUserByUsernameOrEmail($usernameOrEmail)
    {

            $user = $this->findUserByEmail($usernameOrEmail);

            return $user;


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
        $userTest = $this->findUserByUsernameOrEmail($user->getEmail());
        if (!$userTest || $userTest = $user){
            $this->em->persist($user);
            $this->em->flush();
            return true;
        }
        throw new \Exception('User already Exitsts with this username or E-Mail');
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