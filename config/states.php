<?php

return [
    /*
    |--------------------------------------------------------------------------
    | State API Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration manages state API integrations with fallback options
    | for development and production environments.
    |
    */

    'florida' => [
        'enabled' => env('FLORIDA_ENABLED', true),
        'mode' => env('FLORIDA_MODE', 'live'), // live, fallback, mock, disabled
        'wsdl_url' => env('FLORIDA_WSDL_URL', 'https://services.flhsmv.gov/DriverSchoolWebService/wsPrimerComponentService.svc?wsdl'),
        'service_url' => env('FLORIDA_SERVICE_URL'), // HTTP fallback endpoint
        'username' => env('FLORIDA_USERNAME'),
        'password' => env('FLORIDA_PASSWORD'),
        'school_id' => env('FLORIDA_SCHOOL_ID'),
        'instructor_id' => env('FLORIDA_INSTRUCTOR_ID'),
        'timeout' => env('FLORIDA_TIMEOUT', 30),
        'fallback' => [
            'enabled' => env('FLORIDA_FALLBACK_ENABLED', true),
            'simulate_success' => env('FLORIDA_SIMULATE_SUCCESS', true),
            'queue_for_manual' => env('FLORIDA_QUEUE_MANUAL', false),
        ],
    ],

    'california' => [
        'tvcc' => [
            'enabled' => env('CALIFORNIA_TVCC_ENABLED', true),
            'mode' => env('CALIFORNIA_TVCC_MODE', 'live'), // live, mock, disabled
            'url' => env('CALIFORNIA_TVCC_URL', 'https://xsg.dmv.ca.gov/tvcc/tvccservice'),
            'user' => env('CALIFORNIA_TVCC_USER', 'Support@dummiestrafficschool.com'),
            'modality' => env('CALIFORNIA_TVCC_MODALITY', '4T'),
            'timeout' => env('CALIFORNIA_TVCC_TIMEOUT', 30),
            'fallback' => [
                'enabled' => env('CALIFORNIA_TVCC_FALLBACK_ENABLED', true),
                'simulate_success' => env('CALIFORNIA_TVCC_SIMULATE_SUCCESS', true),
            ],
        ],
    ],

    'nevada' => [
        'ntsa' => [
            'enabled' => env('NEVADA_NTSA_ENABLED', true),
            'mode' => env('NEVADA_NTSA_MODE', 'live'), // live, mock, disabled
            'url' => env('NEVADA_NTSA_URL', 'https://secure.ntsa.us/cgi-bin/register.cgi'),
            'school_name' => env('NEVADA_NTSA_SCHOOL_NAME', 'DUMMIES TRAFFIC SCHOOL.COM'),
            'test_name' => env('NEVADA_NTSA_TEST_NAME', 'DUMMIES TRAFFIC SCHOOL.COM - CA'),
            'timeout' => env('NEVADA_NTSA_TIMEOUT', 30),
            'fallback' => [
                'enabled' => env('NEVADA_NTSA_FALLBACK_ENABLED', true),
                'simulate_success' => env('NEVADA_NTSA_SIMULATE_SUCCESS', true),
            ],
        ],
    ],

    'ccs' => [
        'enabled' => env('CCS_ENABLED', true),
        'mode' => env('CCS_MODE', 'live'), // live, mock, disabled
        'url' => env('CCS_URL', 'http://testingprovider.com/ccs/register.jsp'),
        'school_name' => env('CCS_SCHOOL_NAME', 'dummiests'),
        'timeout' => env('CCS_TIMEOUT', 30),
        'fallback' => [
            'enabled' => env('CCS_FALLBACK_ENABLED', true),
            'simulate_success' => env('CCS_SIMULATE_SUCCESS', true),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Mock Response Configuration
    |--------------------------------------------------------------------------
    |
    | Define mock responses for testing and development
    |
    */
    'mocks' => [
        'florida' => [
            'success_response' => [
                'Success' => true,
                'CertificateNumber' => 'FL' . date('Y') . '{{RANDOM_6_DIGITS}}',
                'ResponseCode' => 'SUCCESS',
                'Message' => 'Certificate submitted successfully (MOCK)',
            ],
            'error_response' => [
                'Success' => false,
                'ErrorCode' => 'MOCK_ERROR',
                'ErrorMessage' => 'Mock error for testing',
            ],
        ],
        'california' => [
            'success_response' => [
                'ccSeqNbr' => 'CA' . date('Y') . '{{RANDOM_6_DIGITS}}',
                'ccStatCd' => 'SUCCESS',
                'ccSubTstamp' => now()->toISOString(),
            ],
        ],
        'nevada' => [
            'success_response' => 'Registration submitted successfully',
        ],
        'ccs' => [
            'success_response' => 'Student registration completed',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Development Settings
    |--------------------------------------------------------------------------
    */
    'development' => [
        'log_all_requests' => env('STATE_API_LOG_REQUESTS', false),
        'log_all_responses' => env('STATE_API_LOG_RESPONSES', false),
        'simulate_network_delays' => env('STATE_API_SIMULATE_DELAYS', false),
        'force_fallback_mode' => env('STATE_API_FORCE_FALLBACK', false),
    ],
];