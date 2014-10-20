<?php

namespace Acme\AuthBundle\Entity;

class LoginFormValidate
{
    public $fieldPass;
    public $fieldEmail;

    public function __construct()
    {
    }

    public function getPassword() {
        return $this->fieldPass;
    }

    public function setPassword($password) {
        $this->fieldPass = $password;
    }

    public function setEmail($email) {
        $this->fieldEmail = $email;
    }

    public function getEmail() {
        return $this->fieldEmail;
    }
}