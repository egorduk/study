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

    public function setSum($val)
    {
        return $this->fieldSum = $val;
    }

    public function setDay($val)
    {
        return $this->fieldDay = $val;
    }

    public function setComment($val)
    {
        return $this->fieldComment = $val;
    }

    public function setIsClientDate($val)
    {
        return $this->is_client_date = $val;
    }
}