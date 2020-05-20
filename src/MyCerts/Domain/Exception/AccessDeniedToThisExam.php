<?php

namespace MyCerts\Domain\Exception;

class AccessDeniedToThisExam extends \Exception
{
    protected $message = 'Access denied to this exam';
}