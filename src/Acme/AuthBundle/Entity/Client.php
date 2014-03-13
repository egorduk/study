<?php

namespace Acme\AuthBundle\Entity;
use DMS\Bundle\FilterBundle\Service\Filter as filter;

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