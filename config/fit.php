<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Applications
    |--------------------------------------------------------------------------
    |
    | Configure the applications that verify Forge Invocation Tokens (FITs).
    |
    */

    'applications' => [
        'default' => [
            'appId' => (string) env('FIT_APP_ID', ''),
            'jwksUrl' => (string) env('FIT_JWKS_URL', ''),
        ],
    ],


    /*
    |--------------------------------------------------------------------------
    | Middleware Alias
    |--------------------------------------------------------------------------
    |
    | Define the alias for the FIT validation middleware.
    |
    */

    'middlewareAlias' => 'fit',

    /*
    |--------------------------------------------------------------------------
    | Issuer
    |--------------------------------------------------------------------------
    |
    | Define the expected Forge Invocation Token issuer.
    |
    */

    'issuer' => 'forge/invocation-token',

];
