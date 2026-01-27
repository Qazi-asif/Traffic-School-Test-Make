<?php

return [
    /*
    |--------------------------------------------------------------------------
    | HTTP SOAP Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for HTTP-based SOAP services that work without the
    | PHP SOAP extension. This is useful for shared hosting environments
    | where the SOAP extension is not available.
    |
    */

    'enabled' => env('HTTP_SOAP_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Default Settings
    |--------------------------------------------------------------------------
    */
    'defaults' => [
        'timeout' => env('HTTP_SOAP_TIMEOUT', 30),
        'verify_ssl' => env('HTTP_SOAP_VERIFY_SSL', false),
        'user_agent' => env('HTTP_SOAP_USER_AGENT', 'Laravel HTTP SOAP Client/1.0'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Florida FLHSMV Configuration
    |--------------------------------------------------------------------------
    */
    'florida' => [
        'enabled' => env('FLORIDA_HTTP_SOAP_ENABLED', true),
        'endpoint' => env('FLORIDA_SOAP_ENDPOINT', env('FLORIDA_WSDL_URL')),
        'soap_action' => env('FLORIDA_SOAP_ACTION', 'http://tempuri.org/wsVerifyData'),
        'method' => env('FLORIDA_SOAP_METHOD', 'wsVerifyData'),
        'timeout' => env('FLORIDA_SOAP_TIMEOUT', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | California TVCC Configuration
    |--------------------------------------------------------------------------
    */
    'california_tvcc' => [
        'enabled' => env('CALIFORNIA_TVCC_HTTP_SOAP_ENABLED', true),
        'endpoint' => env('CALIFORNIA_TVCC_ENDPOINT', 'https://xsg.dmv.ca.gov/tvcc/tvccservice'),
        'soap_action' => env('CALIFORNIA_TVCC_SOAP_ACTION', 'http://tempuri.org/submitCertificate'),
        'method' => env('CALIFORNIA_TVCC_SOAP_METHOD', 'submitCertificate'),
        'timeout' => env('CALIFORNIA_TVCC_TIMEOUT', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | Fallback Behavior
    |--------------------------------------------------------------------------
    |
    | What to do when HTTP SOAP fails:
    | - 'soap' - Try native SOAP extension if available
    | - 'mock' - Return mock success response
    | - 'fail' - Return failure response
    |
    */
    'fallback_mode' => env('HTTP_SOAP_FALLBACK_MODE', 'soap'),

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    */
    'logging' => [
        'enabled' => env('HTTP_SOAP_LOGGING_ENABLED', true),
        'log_requests' => env('HTTP_SOAP_LOG_REQUESTS', true),
        'log_responses' => env('HTTP_SOAP_LOG_RESPONSES', true),
        'sanitize_sensitive_data' => env('HTTP_SOAP_SANITIZE_LOGS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Retry Configuration
    |--------------------------------------------------------------------------
    */
    'retry' => [
        'enabled' => env('HTTP_SOAP_RETRY_ENABLED', true),
        'max_attempts' => env('HTTP_SOAP_MAX_RETRIES', 3),
        'delay_seconds' => env('HTTP_SOAP_RETRY_DELAY', 5),
        'backoff_multiplier' => env('HTTP_SOAP_BACKOFF_MULTIPLIER', 2),
    ],
];