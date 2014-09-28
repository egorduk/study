<?php

namespace Acme\SecureBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\EntityRepository;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_ps")
 */
class UserPs extends EntityRepository
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
    private $name;

    /**
     * @ORM\Column(type="string")
     */
    private $num;

    /**
     * @ORM\ManyToOne(targetEntity="Acme\AuthBundle\Entity\UserInfo", inversedBy="link_user_ps", cascade={"all"})
     * @ORM\JoinColumn(name="user_info_id", referencedColumnName="id")
     **/
    private $user_info;

    /**
     * @ORM\ManyToOne(targetEntity="TypePs", inversedBy="link_type_ps", cascade={"all"})
     * @ORM\JoinColumn(name="type_ps_id", referencedColumnName="id")
     **/
    private $type_ps;


    public function __construct(){
    }

    public function setName($val) {
        $this->name = $val;
    }

    public function getName() {
        return $this->name;
    }

    public function setNum($val) {
        $this->num = $val;
    }

    public function getNum() {
        return $this->num;
    }

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setUserInfo($val) {
        $this->user_info = $val;
    }

    public function getUserInfo() {
        return $this->user_info;
    }

    public function setTypePs($val) {
        $this->type_ps = $val;
    }

    public function getTypePs() {
        return $this->type_ps;
    }
}