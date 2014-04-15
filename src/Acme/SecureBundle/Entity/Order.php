<?php

namespace Acme\SecureBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Doctrine\ORM\EntityRepository;
use Zend\I18n\Validator\DateTime;

/**
 * @ORM\Entity
 * @ORM\Table(name="order")
 */
class Order
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
    public $theme;

    /**
     * @ORM\Column(type="date")
     */
    public $date_cxpire;

    /**
     * @ORM\Column(type="date")
     */
    public $date_create;

    /**
     * @ORM\Column(type="string")
     */
    public $describe;

    /**
     * @ORM\Column(type="string")
     */
    public $originality;

    /**
     * @ORM\Column(type="string")
     */
    public $count_sheet;


    public $subject_id;


    public $type_order_id;


    public function __construct(){
        $this->date_create = new \DateTime();
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
        $this->date_cxpire = $date;
    }

    public function getDateExpire()
    {
        return $this->date_cxpire;
    }

    public function setDescribe($describe)
    {
        $this->describe = $describe;
    }

    public function getDescribe()
    {
        return $this->describe;
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
        return $this->subject_id;
    }

    public function setSubject($subject)
    {
        $this->subject_id = $subject;
    }

    public function getTypeOrder()
    {
        return $this->type_order_id;
    }

    public function setTypeOrder($type)
    {
        $this->type_order_id = $type;
    }
}