<?php

namespace Acme\SecureBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use Symfony\Component\Serializer;

/**
 * @ORM\Entity
 * @ORM\Table(name="User")
 */
//class User extends EntityRepository implements AdvancedUserInterface
class User extends EntityRepository implements UserInterface, \Serializable
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     */
    protected $login;

    /**
     * @ORM\Column(type="string")
     */
    protected $password;

    /**
     * @ORM\Column(type="string")
     */
    protected $email;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $date_reg;

    /**
     * @ORM\Column(type="integer")
     */
    protected $role_id;

    /**
     * @ORM\Column(type="string")
     */
    protected $salt;

    /**
     * @ORM\Column(type="integer")
     */
    protected $is_active;

    /**
     * @ORM\Column(type="integer")
     */
    protected $ip_reg;

    /**
     * @ORM\Column(type="integer")
     */
    protected $openid_id;


    public function __construct()
    {
        $this->date_reg = new \DateTime();
        $this->is_active = 0;
        $this->ip_reg = ip2long($_SERVER['REMOTE_ADDR']);
        $this->openid_id = 1;
        $this->role_id = 2;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getUsername()
    {

    }

    public function getIpReg()
    {
        return $this->ip_reg;
    }

    public function getSalt()
    {
        return $this->salt;
    }

    public function setSalt($value)
    {
        $this->salt = $value;
    }

    public function getRole()
    {
        return $this->role_id;
    }

    public function setRole($role)
    {
        $this->role_id = $role;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setPassword($password)
    {
        $this->password = $password;
    }

    public function getLogin()
    {
        return $this->login;
    }

    public function setLogin($login)
    {
        $this->login = $login;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function setDateReg($dateReg)
    {
        $this->date_reg = $dateReg;
    }

    public function getDateReg()
    {
        return $this->date_reg;
    }


    public function getRoles()
    {
        //return $this->getUserRoles()->toArray();
        return array('ROLE_CLIENT');
    }

    public function eraseCredentials()
    {

    }

    public function isAccountNonExpired()
    {
        return true;
    }

    public function isAccountNonLocked()
    {
        return true;
    }

    public function isCredentialsNonExpired()
    {
        return true;
    }

    public function isEnabled()
    {
        return $this->is_active;
    }

    public function setIsEnabled($active)
    {
        $this->is_active = $active;
    }

    /**
     * @see \Serializable::serialize()
     */
    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->login,
            $this->password,
            $this->email
        ));
    }

    /**
     * @see \Serializable::unserialize()
     */
    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->login,
            $this->password,
            $this->email
            ) = unserialize($serialized);
    }
}