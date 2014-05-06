<?php

namespace Acme\SecureBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Doctrine\ORM\EntityRepository;

/**
 * @ORM\Entity
 * @ORM\Table(name="order_file")
 */
class OrderFile extends EntityRepository
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
     * @ORM\Column(type="datetime")
     */
    private $date_upload;

    /**
     * @ORM\Column(type="integer")
     */
    private $size;

    /**
     * @ORM\ManyToOne(targetEntity="UserOrder", inversedBy="link_user_order", cascade={"all"})
     * @ORM\JoinColumn(name="user_order_id", referencedColumnName="id")
     **/
    private $user_order;


    public function __construct(){

    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getDateUpload()
    {
        return $this->date_upload;
    }

    public function setDateUpload($date)
    {
        $this->date_upload = $date;
    }

    public function getSize()
    {
        return $this->size;
    }

    public function setSize($size)
    {
        $this->size = $size;
    }

    public function getUserOrder()
    {
        return $this->user_order;
    }

    public function setUserOrder($userOrder)
    {
        $this->user_order = $userOrder;
    }

}