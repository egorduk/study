<?php

namespace Acme\SecureBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\EntityRepository;

/**
 * @ORM\Entity
 * @ORM\Table(name="subject_order")
 */
class SubjectOrder extends EntityRepository
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
    private $parent_name;

    /**
     * @ORM\Column(type="string")
     */
    private $child_name;

    /**
     * @ORM\OneToMany(targetEntity="UserOrder", mappedBy="subject_order")
     **/
    private $link_subject;


    public function __construct(){
        $this->link_subject = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function setParentName($name)
    {
        $this->parent_name = $name;
    }

    public function getParentName()
    {
        return $this->parent_name;
    }

    public function setChildName($name)
    {
        $this->child_name = $name;
    }

    public function getChildName()
    {
        return $this->child_name;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

}