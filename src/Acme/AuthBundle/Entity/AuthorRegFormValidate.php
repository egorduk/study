<?php

namespace Acme\AuthBundle\Entity;


class AuthorRegFormValidate extends ClientRegFormValidate
{
    public $fieldMobilePhone;
    public $fieldSkype;
    public $fieldIcq;
    public $selectorCountry;

    public function setMobilePhone($phone)
    {
        $this->$fieldMobilePhone = $phone;
    }

    public function getMobilePhone()
    {
        return $this->$fieldMobilePhone;
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


}