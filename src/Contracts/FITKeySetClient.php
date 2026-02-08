<?php

namespace BenColmer\LaravelFITValidator\Contracts;

interface FITKeySetClient
{
    /**
     * Get the JSON Web Key Set.
     */
    public function get(): ?array;
}
