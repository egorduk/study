<?php

namespace Acme\AuthBundle\Entity;

use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;

class ClientFormValidate
{
    public $fieldLogin;
    public $fieldPass;
    public $fieldEmail;
    public $fieldPassApprove;
    public $checkAgreeRules;
    public $current_password;
    public $plainPassword;

}