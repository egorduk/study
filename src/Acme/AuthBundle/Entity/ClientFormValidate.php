<?php

namespace Acme\AuthBundle\Entity;

class ClientFormValidate
{
    public $fieldLogin;
    public $fieldPass;
    public $fieldEmail;
    public $fieldPassApprove;
    public $checkAgreeRules;

    public function __construct()
    {
        //parent::__construct();
    }

    public function getFieldPass()
    {
        return $this->fieldPass;
    }

    public function getFieldApprovePass()
    {
        return $this->fieldPassApprove;
    }

}