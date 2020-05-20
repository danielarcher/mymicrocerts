<?php

namespace MyCerts\Domain\Exception;

class NoAttemptsLeftForThisExam extends \Exception
{
    protected $message = 'No attempts left for this exam';
}