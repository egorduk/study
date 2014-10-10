<?php

namespace Acme\AuthBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="Country")
 */
class Country extends EntityRepository
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
    protected $name;

    /**
     * @ORM\Column(type="string")
     */
    protected $code;

    /**
     * @ORM\Column(type="string")
     */
    protected $mobile_code;

    /**
     * @ORM\OneToMany(targetEntity="Openid", mappedBy="country")
     **/
    protected $link_country;

    /**
     * @ORM\OneToMany(targetEntity="UserInfo", mappedBy="country")
     **/
    protected $link_user_info;

    public function __construct()
    {
        $this->link_country = new \Doctrine\Common\Collections\ArrayCollection();
        $this->link_user_info = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function setCode($code)
    {
        $this->code = $code;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function getMobileCode()
    {
        return $this->mobile_code;
    }

}