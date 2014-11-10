<?php

namespace Acme\SecureBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\EntityRepository;
use Helper\Helper;

/**
 * @ORM\Entity
 * @ORM\Table(name="money_output")
 */
class MoneyOutput extends EntityRepository
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
     * @ORM\Column(type="datetime")
     */
    private $date_output;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date_create;

    /**
     * @ORM\Column(type="integer")
     */
    private $is_done;

    /**
     * @ORM\ManyToOne(targetEntity="UserPs", inversedBy="link_user_ps", cascade={"merge"})
     * @ORM\JoinColumn(name="user_ps_id", referencedColumnName="id")
     **/
    private $user_ps;


    public function __construct(){
        $this->date_create = new \DateTime();
        $this->is_done = 0;
    }

    public function setSum($val) {
        $this->sum = $val;
    }

    public function getSum() {
        return $this->sum;
    }

    public function setDateCreate($val) {
        $this->date_create = $val;
    }

    public function getDateCreate() {
        return $this->date_create;
    }

    public function setDateOutput($val) {
        $this->date_output = $val;
    }

    public function getDateOutput() {
        return $this->date_output;
    }

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setUserPs($val) {
        $this->user_ps = $val;
    }

    public function getUserPs() {
        return $this->user_ps;
    }

    public function getIsDone() {
        return $this->is_done;
    }

    public function setIsDone($val) {
        $this->is_done = $val;
    }
}