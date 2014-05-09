<?php

namespace Acme\AuthBundle\Entity;
use Doctrine\ORM\EntityRepository;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="Openid")
 */
class Openid extends EntityRepository
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="integer")
     */
    protected $uid;

    /**
     * @ORM\Column(type="string")
     */
    protected $profile_url;

    /**
     * @ORM\Column(type="string")
     */
    protected $email;

    /**
     * @ORM\Column(type="string")
     */
    protected $nickname;

    /**
     * @ORM\Column(type="string")
     */
    protected $first_name;

    /**
     * @ORM\Column(type="string")
     */
    protected $identity;

    /**
     * @ORM\Column(type="string")
     */
    protected $photo_big;

    /**
     * @ORM\Column(type="string")
     */
    protected $photo;

    /**
     * @ORM\ManyToOne(targetEntity="Provider", inversedBy="link_provider", cascade={"all"})
     * @ORM\JoinColumn(name="provider_id", referencedColumnName="id")
     **/
    protected $provider;

    /**
     * @ORM\ManyToOne(targetEntity="Country", inversedBy="link_country", cascade={"all"})
     * @ORM\JoinColumn(name="country_id", referencedColumnName="id")
     **/
    protected $country;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="link_openid", cascade={"all"})
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     **/
    protected $user;


    public function __construct()
    {
    }

    public function setUid($uid)
    {
        $this->uid = $uid;
    }

    public function setProfileUrl($url)
    {
        $this->profile_url = $url;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function setNickname($nickname)
    {
        $this->nickname = $nickname;
    }

    public function setFirstName($name)
    {
        $this->first_name = $name;
    }

    public function setIdentity($identity)
    {
        $this->identity = $identity;
    }

    public function setPhotoBig($url)
    {
        $this->photo_big = $url;
    }

    public function setPhoto($url)
    {
        $this->photo = $url;
    }

    public function setProvider($provider)
    {
        $this->provider = $provider;
    }

    public function setCountry($country)
    {
        $this->country = $country;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUser($user)
    {
        $this->user = $user;
    }

    public function getCountry()
    {
        return $this->country;
    }

}