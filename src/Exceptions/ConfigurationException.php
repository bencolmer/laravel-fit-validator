<?php

namespace BenColmer\LaravelFITValidator\Exceptions;

use Exception;
use Throwable;

class ConfigurationException extends Exception
{
    public function __construct(
        string $appKey,
        string $property,
        int $code = 0,
        Throwable|null $previous = null
    ) {
        $msg = "The \"{$property}\" on the \"{$appKey}\" application has not been configured. Please configure this application to validate FIT tokens.";

        return parent::__construct($msg, $code, $previous);
    }
}
