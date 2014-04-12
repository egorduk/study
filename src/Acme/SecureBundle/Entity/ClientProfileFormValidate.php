<?php

namespace Acme\SecureBundle\Entity;


class ClientProfileFormValidate
{
    public $fieldMobilePhone;
    public $fieldStaticPhone;
    public $fieldSkype;
    public $fieldIcq;
    public $selectorCountry;
    public $fieldUsername;
    public $fieldSurname;
    public $fieldLastname;


    public function __construct(){
    }

    public function setMobilePhone($phone)
    {
        $this->fieldMobilePhone = $phone;
    }

    public function getMobilePhone()
    {
        return $this->fieldMobilePhone;
    }

    public function getStaticPhone()
    {
        return $this->fieldStaticPhone;
    }

    public function setSkype($skype)
    {
        $this->fieldSkype = $skype;
    }

    public function getSkype()
    {
        return $this->fieldSkype;
    }

    public function setIcq($icq)
    {
        $this->fieldIcq = $icq;
    }

    public function getIcq()
    {
        return $this->fieldIcq;
    }

    public function getCountry()
    {
        return $this->selectorCountry;
    }

    public function getUsername()
    {
        return $this->fieldUsername;
    }

    public function getSurname()
    {
        return $this->fieldSurname;
    }

    public function getLastname()
    {
        return $this->fieldLastname;
    }


}