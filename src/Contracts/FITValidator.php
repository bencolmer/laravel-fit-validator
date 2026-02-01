<?php

namespace BenColmer\LaravelFITValidator\Contracts;

use Illuminate\Http\Request;

interface FITValidator
{
    /**
     * Validate the Forge Invocation Token (FIT) for the app and return the payload.
     *
     * @throws ValidationException
     * @return array|null
     */
    public function validate(Request|string $input): ?array;
}
