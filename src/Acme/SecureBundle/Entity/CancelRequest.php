<?php

namespace Acme\SecureBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\EntityRepository;

/**
 * @ORM\Entity
 * @ORM\Table(name="cancel_request")
 */
class CancelRequest extends EntityRepository
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
    private $date_create;

    /**
     * @ORM\ManyToOne(targetEntity="UserOrder", inversedBy="link_cancel_request_user_order", cascade={"all"})
     * @ORM\JoinColumn(name="user_order_id", referencedColumnName="id")
     **/
    private $user_order;

    /**
     * @ORM\Column(type="string")
     */
    private $comment;

    /**
     * @ORM\Column(type="integer")
     */
    private $creator;

    /**
     * @ORM\Column(type="string")
     */
    private $verdict;

    /**
     * @ORM\Column(type="integer")
     */
    private $is_together_apply;

    /**
     * @ORM\Column(type="integer")
     */
    private $percent;

    /**
     * @ORM\Column(type="integer")
     */
    private $is_show;

    private $text_percent;
    private $creator_role;


    public function __construct(){
        $this->date_create = new \DateTime();
        $this->date_verdict = new \DateTime(date("Y-m-d H:i:s", strtotime("+3 day")));
        $this->verdict = "";
        $this->is_show = 1;
    }

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getDateCreate() {
        return $this->date_create;
    }

    public function setDateCreate($date) {
        $this->date_create = $date;
    }

    public function setUserOrder($val) {
        $this->user_order = $val;
    }

    public function getUserOrder() {
        return $this->user_order;
    }

    public function setComment($val) {
        $this->comment = $val;
    }

    public function getComment() {
        return wordwrap($this->comment, 60, "\n", true);
    }

    public function setPercent($val) {
        $this->percent = $val;
    }

    public function getPercent() {
        return $this->percent;
    }

    public function setIsTogetherApply($val) {
        $this->is_together_apply = $val;
    }

    public function getIsTogetherApply() {
        return $this->is_together_apply;
    }

    public function setVerdict($val) {
        $this->verdict = $val;
    }

    public function getVerdict() {
        return $this->verdict;
    }

    public function getCreator() {
        return $this->creator;
    }

    public function setCreator($val) {
        $this->creator = $val;
    }

    public function setTextPercent($val) {
        $this->text_percent = $val;
    }

    public function setCreatorRole($val) {
        $this->creator_role = $val;
    }

    public function getCreatorRole() {
        return $this->creator_role;
    }

    public function setIsShow($val) {
        $this->is_show = $val;
    }

    public function getIsShow() {
        return $this->is_show;
    }
}