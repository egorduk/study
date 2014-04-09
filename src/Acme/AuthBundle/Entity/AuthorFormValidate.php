<?php

namespace Acme\AuthBundle\Entity;

//use Acme\AuthBundle\Entity\ClientFormValidate;

class AuthorFormValidate /*extends ClientFormValidate*/
{
    public $fieldMobileTel;

    public function setMobileTel($tel)
    {
        $this->fieldMobileTel = $tel;
    }

    public function getMobileTel()
    {
        return $this->fieldMobileTel;
    }


}