<?php

namespace Acme\SecureBundle\Entity\Author;


class MailOptionsFormValidate
{
    public $fieldOptions;

    public function __construct(){
    }

    public function getOptions() {
        return $this->fieldOptions;
    }

    public function setOptions($val){
        $this->fieldOptions = $val;
    }
}