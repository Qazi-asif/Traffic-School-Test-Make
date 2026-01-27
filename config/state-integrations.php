<?php

return [
    /*
    |--------------------------------------------------------------------------
    | California State Integrations
    |--------------------------------------------------------------------------
    */
    'california' => [
        'tvcc' => [
            'enabled' => env('CALIFORNIA_TVCC_ENABLED', false),
            'url' => env('CALIFORNIA_TVCC_URL', 'https://xsg.dmv.ca.gov/tvcc/tvccservice'),
            'wsdl_url' => env('CALIFORNIA_TVCC_WSDL_URL', 'https://xsg.dmv.ca.gov/tvcc/tvccservice?wsdl'),
            'user' => env('CALIFORNIA_TVCC_USER', 'Support@dummiestrafficschool.com'),
            'password' => env('CALIFORNIA_TVCC_PASSWORD', 'Traffic24'), // Also stored in database
            'modality' => env('CALIFORNIA_TVCC_MODALITY', '4T'),
            'timeout' => env('CALIFORNIA_TVCC_TIMEOUT', 30),
            'environment' => env('CALIFORNIA_TVCC_ENVIRONMENT', 'production'),
        ],
        'ctsi' => [
            'enabled' => env('CALIFORNIA_CTSI_ENABLED', false),
            'callback_url' => env('CALIFORNIA_CTSI_CALLBACK_URL', '/api/ctsi/result'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Nevada State Integrations
    |--------------------------------------------------------------------------
    */
    'nevada' => [
        'ntsa' => [
            'enabled' => env('NEVADA_NTSA_ENABLED', false),
            'url' => env('NEVADA_NTSA_URL', 'https://secure.ntsa.us/cgi-bin/register.cgi'),
            'school_name' => env('NEVADA_NTSA_SCHOOL_NAME', 'DUMMIES TRAFFIC SCHOOL.COM'),
            'test_name' => env('NEVADA_NTSA_TEST_NAME', 'DUMMIES TRAFFIC SCHOOL.COM - CA'),
            'result_url' => env('NEVADA_NTSA_RESULT_URL', '/api/ntsa/result'),
            'timeout' => env('NEVADA_NTSA_TIMEOUT', 30),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | CCS (Court Compliance System) Integration
    |--------------------------------------------------------------------------
    */
    'ccs' => [
        'enabled' => env('CCS_ENABLED', false),
        'url' => env('CCS_URL', 'http://testingprovider.com/ccs/register.jsp'),
        'school_name' => env('CCS_SCHOOL_NAME', 'dummiests'),
        'result_url' => env('CCS_RESULT_URL', '/api/ccs/result'),
        'timeout' => env('CCS_TIMEOUT', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | Florida FLHSMV Integration
    |--------------------------------------------------------------------------
    | 
    | Note: Florida configuration is in config/services.php under 'florida' key
    |--------------------------------------------------------------------------
    */
];