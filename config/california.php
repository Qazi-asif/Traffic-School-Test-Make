<?php

return [
    /*
    |--------------------------------------------------------------------------
    | California TVCC Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for California DMV Traffic Violator Certificate Completion
    | (TVCC) API integration.
    |
    */

    'tvcc' => [
        'enabled' => env('CA_TVCC_ENABLED', false),
        'endpoint' => env('CA_TVCC_ENDPOINT', 'https://xsg.dmv.ca.gov/tvcc/tvccservice'),
        'user_id' => env('CA_TVCC_USER_ID', 'Support@dummiestrafficschool.com'),
        'password' => env('CA_TVCC_PASSWORD'),
        'modality' => '4T', // Fixed value for online traffic school
        'timeout' => 30, // seconds
        'verify_ssl' => env('CA_TVCC_VERIFY_SSL', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | California CTSI Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for California Traffic School Interface (CTSI) integration.
    | CTSI is an XML callback system where courts send completion data.
    |
    */

    'ctsi' => [
        'enabled' => env('CA_CTSI_ENABLED', false),
        'result_url' => env('CA_CTSI_RESULT_URL', env('APP_URL').'/api/ctsi/result'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Retry Configuration
    |--------------------------------------------------------------------------
    */

    'retry' => [
        'max_attempts' => 5,
        'delay_seconds' => 300, // 5 minutes
    ],
];
