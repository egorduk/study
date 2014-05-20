<?php

namespace Acme\SecureBundle\Entity\Client;


class CreateOrderFormValidate
{
    public $fieldTheme;
    public $fieldDateExpire;
    public $fieldTask;
    public $fieldOriginality;
    public $fieldCountSheet;
    public $selectorSubject;
    public $selectorTypeOrder;


    public function __construct(){
    }

    /*public function getSubject()
    {
        return $this->selectorSubject;
    }

    public function getTypeOrder()
    {
        return $this->selectorTypeOrder;
    }*/

}