<?php

namespace MyCerts\Domain\Exception;

use Exception;

class TransactionDeclinedException extends Exception
{
    protected $message = 'Transaction declined';
}