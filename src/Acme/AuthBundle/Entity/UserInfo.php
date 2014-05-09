<?php

namespace Acme\AuthBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_info")
 */
class UserInfo extends EntityRepository
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
    protected $skype;

    /**
     * @ORM\Column(type="string")
     */
    protected $icq;

    /**
     * @ORM\Column(type="string")
     */
    protected $mobile_phone;

    /**
     * @ORM\Column(type="string")
     */
    protected $static_phone;

    /**
     * @ORM\Column(type="string")
     */
    protected $username;

    /**
     * @ORM\Column(type="string")
     */
    protected $surname;

    /**
     * @ORM\Column(type="string")
     */
    protected $lastname;

    /**
     * @ORM\ManyToOne(targetEntity="Country", inversedBy="link_user_info", cascade={"all"})
     * @ORM\JoinColumn(name="country_id", referencedColumnName="id")
     **/
    protected $country;

    /**
     * @ORM\OneToMany(targetEntity="User", mappedBy="userInfo")
     **/
    protected $link_user_info;


    public function __construct()
    {
        $this->skype = "";
        $this->icq = "";
        $this->mobile_phone = "";
        $this->static_phone = "";
        $this->username = "";
        $this->surname = "";
        $this->lastname = "";
        $this->link_user_info = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getSkype()
    {
        return $this->skype;
    }

    public function setSkype($skype)
    {
        $this->skype = $skype;
    }

    public function getIcq()
    {
        return $this->icq;
    }

    public function setIcq($icq)
    {
        $this->icq = $icq;
    }

    public function getMobilePhone()
    {
        return $this->mobile_phone;
    }

    public function setMobilePhone($phone)
    {
        $this->mobile_phone = $phone;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setCountry($country)
    {
        $this->country = $country;
    }

    public function getCountry()
    {
        return $this->country;
    }

    public function setStaticPhone($phone)
    {
        $this->static_phone = $phone;
    }

    public function getStaticPhone()
    {
        return $this->static_phone;
    }

    public function setUsername($name)
    {
        $this->username = $name;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function setSurname($surname)
    {
        $this->surname = $surname;
    }

    public function getSurname()
    {
        return $this->surname;
    }

    public function setLastname($lastname)
    {
        $this->lastname = $lastname;
    }

    public function getLastname()
    {
        return $this->lastname;
    }
}