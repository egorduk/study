<?php

namespace Acme\SecureBundle\Entity;


class Order
{
    public $fieldTheme;
    public $fieldDateExpire;
    public $fieldDescribe;
    public $fieldOriginality;
    public $fieldCountSheet;
    public $selectorSubject;
    public $selectorTypeOrder;


    public function __construct(){
    }

    public function setTheme($theme)
    {
        $this->fieldTheme = $theme;
    }

    public function getTheme()
    {
        return $this->fieldTheme;
    }

    public function setDateExpire($date)
    {
        $this->fieldDateExpire = $date;
    }

    public function getDateExpire()
    {
        return $this->fieldDateExpire;
    }

    public function setDescribe($describe)
    {
        $this->fieldDescribe = $describe;
    }

    public function getDescribe()
    {
        return $this->fieldDescribe;
    }

    public function setOriginality($originality)
    {
        $this->fieldOriginality = $originality;
    }

    public function getOriginality()
    {
        return $this->fieldOriginality;
    }

    public function setCountSheet($count)
    {
        $this->fieldCountSheet = $count;
    }

    public function getCountSheet()
    {
        return $this->fieldCountSheet;
    }

    public function getSubject()
    {
        return $this->selectorSubject;
    }

    public function setSubject($subject)
    {
        $this->selectorSubject = $subject;
    }

    public function getTypeOrder()
    {
        return $this->selectorTypeOrder;
    }


    public function setTypeOrder($type)
    {
        $this->selectorTypeOrder = $type;
    }
}