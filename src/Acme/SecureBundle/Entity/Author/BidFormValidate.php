<?php

namespace Acme\SecureBundle\Entity\Author;


class BidFormValidate
{
    public $fieldSum;
    public $fieldDay;
    public $fieldComment;
    public $is_client_date;

    public function __construct(){
    }

    public function getFieldSum()
    {
        return $this->fieldSum;
    }

    public function getFieldDay()
    {
        return $this->fieldDay;
    }

    public function getFieldComment()
    {
        return $this->fieldComment;
    }

    public function getIsClientDate()
    {
        return $this->is_client_date;
    }


}