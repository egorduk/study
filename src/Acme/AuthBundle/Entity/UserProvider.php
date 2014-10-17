<?php

namespace Acme\AuthBundle\Entity;

use Doctrine\ORM\NoResultException;
use Helper\Helper;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Serializer;

//class User extends EntityRepository implements AdvancedUserInterface
class UserProvider implements UserProviderInterface
{

    public function loadUserByUsername($username)
    {
        // make a call to your webservice here
        $userData = new User();
        // pretend it returns an array on success, false if there is no user

        if ($userData) {
            $password = '...';

            // ...

            return new User();
        }

        throw new UsernameNotFoundException(
            sprintf('Username "%s" does not exist.', $username)
        );
    }

    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(
                sprintf('Instances of "%s" are not supported.', get_class($user))
            );
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    public function supportsClass($class)
    {
        return $class === 'Acme\WebserviceUserBundle\Security\User\WebserviceUser';
    }
}