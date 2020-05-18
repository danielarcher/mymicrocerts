<?php

namespace MyCerts\Domain\Exception;

class ExamAlreadyFinished extends \Exception
{
    protected $message = 'Exam already finished';
}