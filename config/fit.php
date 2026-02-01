<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Issuer
    |--------------------------------------------------------------------------
    |
    | Define the expected Forge Invocation Token issuer.
    |
    */

    'issuer' => 'forge/invocation-token',

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

];
