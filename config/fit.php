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
    | The alias for the FIT validation middleware.
    |
    */

    'middlewareAlias' => 'fit',

    /*
    |--------------------------------------------------------------------------
    | Issuer
    |--------------------------------------------------------------------------
    |
    | The expected Forge Invocation Token issuer.
    |
    */

    'issuer' => 'forge/invocation-token',

    /*
    |--------------------------------------------------------------------------
    | JWKS Cache Duration
    |--------------------------------------------------------------------------
    |
    | The JSON Web Key Set cache duration in seconds.
    |
    */

    'jwksCacheDuration' => 60 * 5,

];
