<?php

namespace Acme\AuthBundle\Entity;
use DMS\Filter\Rules as Filter;

class Client
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
    public $email;
}