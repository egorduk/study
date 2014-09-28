<?php

namespace Acme\SecureBundle\Entity\Author;


class CreatePsFormValidate
{
    public $fieldType;
    public $fieldNum;

    public function __construct(){
    }

    public function getType() {
        return $this->fieldType;
    }

    public function getNum() {
        return $this->fieldNum;
    }

    public function setType($val) {
        $this->fieldType = $val;
    }

    public function setNum($val){
        $this->fieldNum = $val;
    }
}