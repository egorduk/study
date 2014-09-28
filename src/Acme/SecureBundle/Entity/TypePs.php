<?php

namespace Acme\SecureBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\EntityRepository;

/**
 * @ORM\Entity
 * @ORM\Table(name="type_ps")
 */
class TypePs extends EntityRepository
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
    private $code;

    /**
     * @ORM\Column(type="string")
     */
    private $info;

    /**
     * @ORM\OneToMany(targetEntity="UserPs", mappedBy="type_ps")
     **/
    protected $link_type_ps;



    public function __construct(){
        $this->link_type_ps = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function setName($val) {
        $this->name = $val;
    }

    public function getName() {
        return $this->name;
    }

    public function setInfo($val) {
        $this->info = $val;
    }

    public function getInfo() {
        return $this->info;
    }

    public function getId() {
        return $this->id;
    }

    public function setId($val) {
        $this->id = $val;
    }

    public function getCode() {
        return $this->code;
    }

    public function setCode($val) {
        $this->code = $val;
    }
}