<?php

namespace MyCerts\Domain\Exception;

/**
 * Class ExamAlreadyFinished
 * @package MyCerts\Domain\Exception
 */
class ExamAlreadyFinished extends \Exception
{
    /**
     * @var int
     */
    protected $code = 409;
    /**
     * @var string
     */
    protected $message = 'Exam already finished';
}