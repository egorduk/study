<?php

namespace Acme\AuthBundle\Entity;


class AuthorRegFormValidate extends ClientRegFormValidate
{
    public $fieldMobileTel;
    public $fieldSkype;
    public $fieldIcq;
    public $choice;

    public function setMobileTel($tel)
    {
        $this->fieldMobileTel = $tel;
    }

    public function getMobileTel()
    {
        return $this->fieldMobileTel;
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


}