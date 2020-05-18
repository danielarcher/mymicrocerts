<?php

namespace MyCerts\Domain\Exception;

class UserAlreadyHaveThisCertification extends \Exception
{
    protected $message = 'User already have this certificate';
}