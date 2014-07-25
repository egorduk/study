<?php

namespace Acme\SecureBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\EntityRepository;

/**
 * @ORM\Entity
 * @ORM\Table(name="webchat_message")
 */
class WebchatMessage extends EntityRepository
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date_write;

    /**
     * @ORM\Column(type="string")
     */
    private $message;

    /**
     * @ORM\ManyToOne(targetEntity="Acme\AuthBundle\Entity\User", inversedBy="link_webchat_user", cascade={"all"})
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     **/
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="UserOrder", inversedBy="link_webchat_user_order", cascade={"all"})
     * @ORM\JoinColumn(name="user_order_id", referencedColumnName="id")
     **/
    protected $user_order;


    public function __construct(){
        $this->date_write = new \DateTime();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getDateWrite()
    {
        return $this->date_write;
    }

    public function setDateWrite($date)
    {
        $this->date_write = $date;
    }

    public function setUser($user)
    {
        $this->user = $user;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUserOrder($order)
    {
        $this->user_order = $order;
    }

    public function getUserOrder()
    {
        return $this->user_order;
    }

    public function setMessage($msg)
    {
        $this->message = $msg;
    }

    public function getMessage()
    {
        return $this->message;
    }
}