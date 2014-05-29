<?php

namespace Acme\SecureBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\EntityRepository;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_choice")
 */
class UserChoice extends EntityRepository
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
    private $date_choice;

    /**
     * @ORM\ManyToOne(targetEntity="Acme\AuthBundle\Entity\User", inversedBy="link_user_choice", cascade={"all"})
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     **/
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="UserBid", inversedBy="link_user_bid", cascade={"all"})
     * @ORM\JoinColumn(name="user_bid_id", referencedColumnName="id")
     **/
    private $user_bid;


    public function __construct(){
        $this->$date_choice = new \DateTime();
    }


    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getDateChoice()
    {
        return $this->date_choice;
    }

    public function setDateChoice($date)
    {
        $this->date_choice = $date;
    }

    public function setUser($user)
    {
        $this->user = $user;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUserBid($userBid)
    {
        $this->user_bid = $userBid;
    }

    public function getUserBid()
    {
        return $this->user_bid;
    }

}