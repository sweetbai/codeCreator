<?php

namespace commons\framework\systemError;
use Throwable;

/**
 * User: sweetbai
 * Date: 2017/3/26
 * Time: 15:04
 */
class ForWriteLogException extends \Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}