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
    protected $mobile_tel;


    public function __construct()
    {
        $this->skype = "";
        $this->icq = "";
        $this->mobile_tel = "";
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

    public function getMobileTel()
    {
        return $this->mobile_tel;
    }

    public function setMobileTel($tel)
    {
        $this->mobile_tel = $tel;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }
}