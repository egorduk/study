<?php

namespace Acme\SecureBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\EntityRepository;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_bid")
 */
class UserBid extends EntityRepository
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $sum;

    /**
     * @ORM\Column(type="integer")
     */
    private $day;

    /**
     * @ORM\Column(type="string")
     */
    private $comment;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date_bid;

    /**
     * @ORM\Column(type="integer")
     */
    private $is_client_date;

    /**
     * @ORM\Column(type="integer")
     */
    private $is_select_author;

    /**
     * @ORM\Column(type="integer")
     */
    private $is_show_client;

    /**
     * @ORM\Column(type="integer")
     */
    private $is_show_author;

    /**
     * @ORM\ManyToOne(targetEntity="Acme\AuthBundle\Entity\User", inversedBy="link_user_bid", cascade={"all"})
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     **/
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="UserOrder", inversedBy="link_user_order", cascade={"all"})
     * @ORM\JoinColumn(name="user_order_id", referencedColumnName="id")
     **/
    private $user_order;

    /**
     * @ORM\OneToMany(targetEntity="UserChoice", mappedBy="user_bid")
     **/
    protected $link_user_bid;


    public function __construct(){
        $this->date_bid = new \DateTime();
        $this->is_client_date = 0;
        $this->is_show_author = 1;
        $this->is_show_client = 1;
        $this->day = 0;
        $this->is_select_author = 0;
        $this->link_user_bid = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function setSum($sum)
    {
        $this->sum = $sum;
    }

    public function getSum()
    {
        return $this->sum;
    }

    public function setDay($day)
    {
        $this->day = $day;
    }

    public function getDay()
    {
        return $this->day;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getDateBid()
    {
        return $this->date_bid;
    }

    public function setDateBid($date)
    {
        $this->date_bid = $date;
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

    public function getIsClientDate()
    {
        return $this->is_client_date;
    }

    public function getComment()
    {
        return $this->comment;
    }

    public function setComment($comment)
    {
        $this->comment = $comment;
    }

    public function setIsClientDate($val)
    {
        $this->is_client_date = $val;
    }

    public function setIsSelectAuthor($val)
    {
        $this->is_select_author = $val;
    }

    public function getIsSelectAuthor()
    {
        return $this->is_select_author;
    }

    public function getUserOrder()
    {
        return $this->user_order;
    }

    public function setIsShowAuthor($val)
    {
        $this->is_show_author = $val;
    }

    public function getIsShowAuthor()
    {
        return $this->is_show_author;
    }

    public function setIsShowClient($val)
    {
        $this->is_show_client = $val;
    }

    public function getIsShowClient()
    {
        return $this->is_show_client;
    }
}