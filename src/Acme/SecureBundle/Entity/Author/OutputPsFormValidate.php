<?php

namespace Acme\SecureBundle\Entity\Author;


class OutputPsFormValidate
{
    public $fieldSum;
    public $fieldType;
    public $fieldComment;
    public $user;

    public function __construct($user) {
        $this->user = $user;
    }

    public function getType() {
        return $this->fieldType;
    }

    public function getSum() {
        return $this->fieldSum;
    }

    public function getComment() {
        return $this->fieldComment;
    }

    public function getUser() {
        return $this->user;
    }

    public function setType($val) {
        $this->fieldType = $val;
    }

    public function setSum($val) {
        $this->fieldSum = $val;
    }

    public function setComment($val) {
        $this->fieldComment = $val;
    }
}