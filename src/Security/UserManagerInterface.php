<?php

namespace App\Security;

use App\Entity\User;

interface UserManagerInterface
{
    /**
     * Creates an empty user instance.
     *
     * @return User
     */
    public function createUser();

    /**
     * Deletes a user.
     */
    public function deleteUser(User $user);

    /**
     * Finds one user by the given criteria.
     *
     * @return User|null
     */
    public function findUserBy(array $criteria);

    /**
     * Find a user by its username.
     *
     * @param string $username
     *
     * @return User|null
     */
    public function findUserByUsername($username);

    /**
     * Finds a user by its email.
     *
     * @param string $email
     *
     * @return User|null
     */
    public function findUserByEmail($email);

    /**
     * Finds a user by its username or email.
     *
     * @param string $usernameOrEmail
     *
     * @return User|null
     */
    public function findUserByUsernameOrEmail($usernameOrEmail);

    /**
     * Finds a user by its confirmationToken.
     *
     * @param string $token
     *
     * @return User|null
     */
    public function findUserByConfirmationToken($token);

    /**
     * Returns a collection with all user instances.
     *
     * @return \Traversable
     */
    public function findUsers();

    /**
     * Returns the user's fully qualified class name.
     *
     * @return string
     */
    public function getClass();

    /**
     * Reloads a user.
     */
    public function reloadUser(User $user);

    /**
     * Updates a user.
     */
    public function updateUser(User $user);

    /**
     * Updates the canonical username and email fields for a user.
     */
    public function updateCanonicalFields(User $user);

    /**
     * Updates a user password if a plain password is set.
     */
    public function updatePassword(User $user);
}