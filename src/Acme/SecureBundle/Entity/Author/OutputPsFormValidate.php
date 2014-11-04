<?php

namespace Acme\SecureBundle\Entity\Author;


class OutputPsFormValidate
{
    public $fieldSum;
    public $fieldType;
    public $fieldComment;
    //public $fieldHiddenPsId;

    public function __construct() {
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

    /*public function getHiddenPsId() {
        return $this->fieldHiddenPsId;
    }*/

    public function setType($val) {
        $this->fieldType = $val;
    }

    public function setSum($val) {
        $this->fieldSum = $val;
    }

    public function setComment($val) {
        $this->fieldComment = $val;
    }

    /*public function setHiddenPsId($val) {
        $this->fieldHiddenPsId = $val;
    }*/
}