<?php

namespace Acme\SecureBundle\Entity\Author;


class CreatePsFormValidate
{
    public $fieldType;
    public $fieldNum;
    public $fieldName;
    public $fieldHiddenPsId;

    public function __construct(){
    }

    public function getType() {
        return $this->fieldType;
    }

    public function getNum() {
        return $this->fieldNum;
    }

    public function getName() {
        return $this->fieldName;
    }

    public function getHiddenPsId() {
        return $this->fieldHiddenPsId;
    }

    public function setType($val) {
        $this->fieldType = $val;
    }

    public function setNum($val){
        $this->fieldNum = $val;
    }

    public function setName($val){
        $this->fieldName = $val;
    }

    public function setHiddenPsId($val){
        $this->fieldHiddenPsId = $val;
    }
}