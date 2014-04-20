<?php

namespace Acme\SecureBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Doctrine\ORM\EntityRepository;

/**
 * @ORM\Entity
 * @ORM\Table(name="User_Order")
 */
class UserOrder extends EntityRepository
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
    private $num;

    /**
     * @ORM\Column(type="string")
     */
    private $theme;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date_expire;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date_create;

    /**
     * @ORM\Column(type="string")
     */
    private $task;

    /**
     * @ORM\Column(type="string")
     */
    private $originality;

    /**
     * @ORM\Column(type="string")
     */
    private $count_sheet;

    /**
     * @ORM\ManyToOne(targetEntity="Subject", inversedBy="link_subject", cascade={"all"})
     * @ORM\JoinColumn(name="subject_id", referencedColumnName="id")
     **/
    private $subject;

    /**
     * @ORM\ManyToOne(targetEntity="TypeOrder", inversedBy="link_type_order", cascade={"all"})
     * @ORM\JoinColumn(name="type_order_id", referencedColumnName="id")
     **/
    private $type_order;

    /**
     * @ORM\ManyToOne(targetEntity="Acme\AuthBundle\Entity\User", inversedBy="link_user", cascade={"all"})
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     **/
    protected $user;

    /**
     * @ORM\Column(type="integer")
     */
    private $is_show;


    public function __construct($container){
        $this->date_create = new \DateTime();
        $this->is_show = 1;

        $em = $container->get('doctrine')->getManager();
        $query = $em->getRepository('AcmeSecureBundle:UserOrder')->createQueryBuilder('s');
        $query->select('MAX(s.num) AS max_num');
        $obj = $query->getQuery()->getResult();
        $num = $obj[0]['max_num'];
        $num++;
        $this->num = $num;
    }

    public function setTheme($theme)
    {
        $this->theme = $theme;
    }

    public function getTheme()
    {
        return $this->theme;
    }

    public function setDateExpire($date)
    {
        $format = 'd/m/Y';
        $date = \DateTime::createFromFormat($format, $date);
        $dietStartDate = $date->format('Y-m-d H:i:s');
        $dietStartDate = new \Datetime($dietStartDate);
        $this->date_expire = $dietStartDate;
    }

    public function getDateExpire()
    {
        return $this->date_expire;
    }

    public function setTask($task)
    {
        $this->task = $task;
    }

    public function getTask()
    {
        return $this->task;
    }

    public function setOriginality($originality)
    {
        $this->originality = $originality;
    }

    public function getOriginality()
    {
        return $this->originality;
    }

    public function setCountSheet($count)
    {
        $this->count_sheet = $count;
    }

    public function getCountSheet()
    {
        return $this->count_sheet;
    }

    public function getSubject()
    {
        return $this->subject;
    }

    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    public function getTypeOrder()
    {
        return $this->type_order;
    }

    public function setTypeOrder($type)
    {
        $this->type_order = $type;
    }

    public function setNum($num)
    {
        $this->num = $num;
    }

    public function getNum()
    {
        return $this->num;
    }

    public function setIsShow($val)
    {
        $this->is_show = $val;
    }

    public function getIsShow()
    {
        return $this->is_show;
    }

    public function setUser($user)
    {
        $this->user = $user;
    }

    public function getUser()
    {
        return $this->user;
    }
}