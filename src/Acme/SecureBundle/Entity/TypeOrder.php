<?php

namespace Acme\SecureBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\EntityRepository;

/**
 * @ORM\Entity
 * @ORM\Table(name="type_order")
 */
class TypeOrder extends EntityRepository
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
     * @ORM\OneToMany(targetEntity="UserOrder", mappedBy="type_order")
     **/
    private $link_type_order;


    public function __construct() {
        $this->link_type_order = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function setName($val) {
        $this->name = $val;
    }

    public function getName() {
        return $this->name;
    }

    public function getId() {
        return $this->id;
    }

    public function setId($val) {
        $this->id = $val;
    }

    public function setCode($val) {
        $this->code = $val;
    }

    public function getCode() {
        return $this->code;
    }

}