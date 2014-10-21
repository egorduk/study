<?php

namespace Acme\SecureBundle\Entity;


class CancelRequestFormValidate
{
    public $fieldComment;
    public $fieldPercent;
    public $fieldIsTogetherApply;

    public function __construct(){
    }

    public function getComment() {
        return $this->fieldComment;
    }

    public function getPercent() {
        return $this->fieldPercent;
    }

    public function getIsTogetherApply() {
        return $this->fieldIsTogetherApply;
    }

    public function setComment($val) {
        $this->fieldComment = $val;
    }

    public function setPercent($val){
        $this->fieldPercent = $val;
    }

    public function setIsTogetherApply($val){
        $this->fieldIsTogetherApply = $val;
    }
}