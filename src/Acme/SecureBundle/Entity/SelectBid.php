<?php

namespace Acme\SecureBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\EntityRepository;

/**
 * @ORM\Entity
 * @ORM\Table(name="select_bid")
 */
class SelectBid extends EntityRepository
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
    private $date_select;

    /**
     * @ORM\ManyToOne(targetEntity="Acme\AuthBundle\Entity\User", inversedBy="link_select_user", cascade={"all"})
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     **/
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="UserBid", inversedBy="link_select_user_bid", cascade={"all"})
     * @ORM\JoinColumn(name="user_bid_id", referencedColumnName="id")
     **/
    private $user_bid;


    public function __construct(){
        $this->date_select = new \DateTime();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getDateSelect()
    {
        return $this->date_select;
    }

    public function setDateSelect($date)
    {
        $this->date_select = $date;
    }

    public function setUser($user)
    {
        $this->user = $user;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUserBid($order)
    {
        $this->user_bid = $order;
    }

    public function getUserBid()
    {
        return $this->user_bid;
    }
}