<?php

namespace Acme\AuthBundle\Entity;
use DMS\Filter\Rules as Filter;

class ClientValidate
{
    /**
     * @Filter\StripTags()
     * @Filter\Trim()
     * @Filter\StripNewlines()
     *
     * @var string
     */
    public $fieldLogin;
    public $fieldPass;
    public $fieldEmail;
    public $fieldPassApprove;
    public $checkAgreeRules;
    //public $fielddCaptcha;
}