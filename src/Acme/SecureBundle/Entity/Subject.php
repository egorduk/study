<?php

namespace Acme\SecureBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Doctrine\ORM\EntityRepository;

/**
 * @ORM\Entity
 * @ORM\Table(name="subject")
 */
class Subject extends EntityRepository
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
    public $parent_name;

    /**
     * @ORM\Column(type="string")
     */
    public $child_name;

    /**
     * @ORM\OneToMany(targetEntity="UserOrder", mappedBy="subject")
     **/
    public $link_subject;


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