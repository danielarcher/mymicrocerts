<?php

namespace MyCerts\Domain\Exception;

class AttemptNotFound extends \Exception
{
    protected $message = 'Attempt not found';
}