<?php

namespace BenColmer\LaravelFITValidator\Exceptions;

use Exception;
use Throwable;

class ValidationException extends Exception
{
    public function __construct(
        string|Exception $err = "",
        int $code = 0,
        Throwable|null $previous = null
    ) {
        $msg = is_string($err) ? $err : $err->getMessage();

        return parent::__construct($msg, $code, $previous);
    }
}
