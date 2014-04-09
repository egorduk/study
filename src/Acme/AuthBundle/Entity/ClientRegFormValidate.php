<?php

namespace Acme\AuthBundle\Entity;

class ClientRegFormValidate
{
    public $fieldLogin;
    public $fieldPass;
    public $fieldPassApprove;
    public $fieldEmail;
    public $checkAgreeRules;

    public function __construct()
    {
    }

    public function getPassword()
    {
        return $this->fieldPass;
    }

    public function getApprovePassword()
    {
        return $this->fieldPassApprove;
    }

    public function setLogin($login)
    {
        $this->fieldLogin = $login;
    }

    public function getLogin()
    {
        return $this->fieldLogin;
    }

    public function setEmail($email)
    {
        $this->fieldEmail = $email;
    }

    public function getEmail()
    {
        return $this->fieldEmail;
    }


}