<?php

return [
    /*
    |--------------------------------------------------------------------------
    | California TVCC Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for California Traffic Violator Certificate Completion
    | (TVCC) API integration.
    |
    */

    'enabled' => env('CALIFORNIA_TVCC_ENABLED', false),
    
    'wsdl_path' => resource_path('wsdl/TvccServiceImplService.wsdl'),
    
    'endpoint' => env('CALIFORNIA_TVCC_URL', 'https://xsg.dmv.ca.gov/tvcc/tvccservice'),
    
    'credentials' => [
        'username' => env('CALIFORNIA_TVCC_USER', 'Support@dummiestrafficschool.com'),
        'password' => env('CALIFORNIA_TVCC_PASSWORD', 'Traffic24'), // Also stored in database
    ],
    
    'soap_options' => [
        'trace' => true,
        'exceptions' => true,
        'connection_timeout' => env('CALIFORNIA_TVCC_TIMEOUT', 30),
        'cache_wsdl' => 0, // WSDL_CACHE_NONE
        'soap_version' => 1, // SOAP_1_1,
        'encoding' => 'UTF-8',
    ],
    
    'ssl_options' => [
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true,
    ],
    
    'logging' => [
        'enabled' => env('CALIFORNIA_TVCC_LOG_ENABLED', true),
        'log_requests' => env('CALIFORNIA_TVCC_LOG_REQUESTS', true),
        'log_responses' => env('CALIFORNIA_TVCC_LOG_RESPONSES', true),
    ],
    
    'fallback' => [
        'enabled' => env('CALIFORNIA_TVCC_FALLBACK_ENABLED', true),
        'mock_mode' => env('CALIFORNIA_TVCC_MODE', 'live') === 'mock',
    ],
];