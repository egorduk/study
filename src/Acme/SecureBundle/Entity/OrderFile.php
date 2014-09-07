<?php

namespace Acme\SecureBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
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
    private $is_delete;

    /**
     * @ORM\Column(type="string")
     */
    private $size;

    /**
     * @ORM\ManyToOne(targetEntity="UserOrder", inversedBy="link_order_file", cascade={"all"})
     * @ORM\JoinColumn(name="user_order_id", referencedColumnName="id")
     **/
    private $user_order;

    /**
     * @ORM\ManyToOne(targetEntity="Acme\AuthBundle\Entity\User", inversedBy="link_order_file_user", cascade={"all"})
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     **/
    private $user;

    private $thumbnail_url;
    private $url;

    public function __construct(){
        $this->date_upload = new \DateTime();
        $this->is_delete = 0;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function getName() {
        return $this->name;
    }

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getDateUpload() {
        return $this->date_upload;
    }

    public function setDateUpload($date) {
        $this->date_upload = $date;
    }

    public function getSize() {
        return $this->size;
    }

    public function setSize($size) {
        $this->size = $size;
    }

    public function getUserOrder() {
        return $this->user_order;
    }

    public function setUserOrder($order) {
        $this->user_order = $order;
    }

    public function getUser() {
        return $this->user;
    }

    public function setUser($val) {
        $this->user = $val;
    }

    public function getIsDelete() {
        return $this->is_delete;
    }

    public function setIsDelete($val) {
        $this->is_delete = $val;
    }

    public function getThumbnailUrl() {
        return $this->thumbnail_url;
    }

    public function setThumbnailUrl($val) {
        $this->thumbnail_url = $val;
    }

    public function getUrl() {
        return $this->url;
    }

    public function setUrl($val) {
        $this->url = $val;
    }
}