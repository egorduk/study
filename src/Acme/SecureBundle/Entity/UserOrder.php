<?php

namespace Acme\SecureBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Doctrine\ORM\EntityRepository;

/**
 * @ORM\Entity
 * @ORM\Table(name="User_order")
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
    public $num;

    /**
     * @ORM\Column(type="string")
     */
    public $theme;

    /**
     * @ORM\Column(type="date")
     */
    public $date_expire;

    /**
     * @ORM\Column(type="date")
     */
    public $date_create;

    /**
     * @ORM\Column(type="string")
     */
    public $task;

    /**
     * @ORM\Column(type="string")
     */
    public $originality;

    /**
     * @ORM\Column(type="string")
     */
    public $count_sheet;

    /**
     * @ORM\ManyToOne(targetEntity="subject", inversedBy="link_subject", cascade={"all"})
     * @ORM\JoinColumn(name="subject_id", referencedColumnName="id")
     **/
    public $subject;

    /**
     * @ORM\ManyToOne(targetEntity="TypeOrder", inversedBy="link_type_order", cascade={"all"})
     * @ORM\JoinColumn(name="type_order_id", referencedColumnName="id")
     **/
    public $type_order;


    public function __construct($container){
        $this->date_expire = new \DateTime();
        $this->date_create = new \DateTime();
        $this->num = 1;

        /*$em = $container->get('doctrine')->getManager();
        $order = $em->getRepository('AcmeSecureBundle:UserOrder')
            ->findOneById(1);
        $num = $order->getNum();
        $num++;
        $this->num = $num;*/
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
        $this->date_expire = $date;
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
}