<?php

namespace Acme\SecureBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\EntityRepository;
use Helper\Helper;

/**
 * @ORM\Entity
 * @ORM\Table(name="mail_option")
 */
class MailOption extends EntityRepository
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
    private $new_orders;

    /**
     * @ORM\Column(type="integer")
     */
    private $chat_response;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date_edit;

    /**
     * @ORM\ManyToOne(targetEntity="Acme\AuthBundle\Entity\User", inversedBy="link_mail_option", cascade={"merge"})
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     **/
    private $user;


    public function __construct(){
        $this->date_edit = Helper::getFormatDateForInsert("0000-00-00 00:00:00", "Y-m-d H:i:s");
    }

    public function setChatResponse($val) {
        $this->chat_response = $val;
    }

    public function getChatResponse() {
        return $this->chat_response;
    }

    public function setNewOrders($val) {
        $this->new_orders = $val;
    }

    public function getNewOrders() {
        return $this->new_orders;
    }

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setUser($val) {
        $this->user = $val;
    }

    public function getUser() {
        return $this->user;
    }

    public function getDateEdit() {
        return $this->date_edit;
    }

    public function setDateEdit($val) {
        $this->date_edit = $val;
    }
}