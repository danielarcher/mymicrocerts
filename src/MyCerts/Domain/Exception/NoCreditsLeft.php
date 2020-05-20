<?php

namespace MyCerts\Domain\Exception;

class NoCreditsLeft extends \Exception
{
    protected $message = 'No credits left';
}