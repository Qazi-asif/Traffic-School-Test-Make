<?php

return [
    /*
    |--------------------------------------------------------------------------
    | State Integration Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for state-specific certificate submission integrations.
    | Each state has unique API endpoints, credentials, and requirements.
    |
    */

    'florida' => [
        'enabled' => env('FLORIDA_INTEGRATION_ENABLED', false),
        'dicds' => [
            'soap_url' => env('FLORIDA_DICDS_SOAP_URL', 'https://dicds.flhsmv.gov/soap/certificate'),
            'username' => env('FLORIDA_DICDS_USERNAME'),
            'password' => env('FLORIDA_DICDS_PASSWORD'),
            'school_id' => env('FLORIDA_DICDS_SCHOOL_ID'),
            'timeout' => env('FLORIDA_DICDS_TIMEOUT', 30),
            'retry_attempts' => 3,
            'retry_delay' => [60, 300, 900], // 1 min, 5 min, 15 min
        ],
        'requirements' => [
            'minimum_score' => 80,
            'course_types' => ['BDI', 'ADI'],
            'driver_license_format' => '/^[A-Z]\d{12}$/',
            'required_fields' => [
                'certificate_number',
                'student_name',
                'driver_license_number',
                'completion_date',
                'final_exam_score'
            ],
        ],
    ],

    'missouri' => [
        'enabled' => env('MISSOURI_INTEGRATION_ENABLED', false),
        'dor' => [
            'api_url' => env('MISSOURI_DOR_API_URL', 'https://api.dor.mo.gov/defensive-driving'),
            'username' => env('MISSOURI_DOR_USERNAME'),
            'password' => env('MISSOURI_DOR_PASSWORD'),
            'school_id' => env('MISSOURI_SCHOOL_ID'),
            'timeout' => env('MISSOURI_DOR_TIMEOUT', 30),
            'retry_attempts' => 3,
            'retry_delay' => [60, 300, 900],
        ],
        'requirements' => [
            'minimum_score' => 70,
            'minimum_hours' => 8,
            'form_4444_required' => true,
            'required_fields' => [
                'certificate_number',
                'student_name',
                'student_address',
                'completion_date',
                'final_exam_score',
                'course_hours'
            ],
        ],
    ],

    'texas' => [
        'enabled' => env('TEXAS_INTEGRATION_ENABLED', false),
        'tdlr' => [
            'api_url' => env('TEXAS_TDLR_API_URL', 'https://api.tdlr.texas.gov/defensive-driving'),
            'username' => env('TEXAS_TDLR_USERNAME'),
            'password' => env('TEXAS_TDLR_PASSWORD'),
            'provider_id' => env('TEXAS_PROVIDER_ID'),
            'timeout' => env('TEXAS_TDLR_TIMEOUT', 30),
            'retry_attempts' => 3,
            'retry_delay' => [60, 300, 900],
        ],
        'requirements' => [
            'minimum_score' => 75,
            'minimum_hours' => 6,
            'driver_license_format' => '/^\d{8}$/',
            'required_fields' => [
                'certificate_number',
                'student_name',
                'driver_license_number',
                'completion_date',
                'final_exam_score',
                'course_hours'
            ],
        ],
    ],

    'delaware' => [
        'enabled' => env('DELAWARE_INTEGRATION_ENABLED', false),
        'dmv' => [
            'api_url' => env('DELAWARE_DMV_API_URL', 'https://api.dmv.delaware.gov/defensive-driving'),
            'username' => env('DELAWARE_DMV_USERNAME'),
            'password' => env('DELAWARE_DMV_PASSWORD'),
            'school_id' => env('DELAWARE_SCHOOL_ID'),
            'timeout' => env('DELAWARE_DMV_TIMEOUT', 30),
            'retry_attempts' => 3,
            'retry_delay' => [60, 300, 900],
        ],
        'requirements' => [
            'minimum_score' => 80,
            'course_types' => ['3hr', '6hr'],
            'quiz_rotation_required' => true,
            'required_fields' => [
                'certificate_number',
                'student_name',
                'completion_date',
                'final_exam_score',
                'course_type',
                'course_hours'
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Global Settings
    |--------------------------------------------------------------------------
    */

    'global' => [
        'auto_submit_enabled' => env('AUTO_STATE_SUBMISSION_ENABLED', false),
        'auto_submit_delay' => env('AUTO_STATE_SUBMISSION_DELAY', 300), // 5 minutes
        'max_retry_attempts' => 3,
        'notification_email' => env('STATE_INTEGRATION_NOTIFICATION_EMAIL'),
        'log_all_requests' => env('LOG_STATE_INTEGRATION_REQUESTS', true),
        'test_mode' => env('STATE_INTEGRATION_TEST_MODE', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue Configuration
    |--------------------------------------------------------------------------
    */

    'queue' => [
        'connection' => env('STATE_INTEGRATION_QUEUE_CONNECTION', 'database'),
        'queue' => env('STATE_INTEGRATION_QUEUE_NAME', 'state-submissions'),
        'timeout' => 300, // 5 minutes
        'retry_after' => 600, // 10 minutes
    ],

    /*
    |--------------------------------------------------------------------------
    | Error Handling
    |--------------------------------------------------------------------------
    */

    'error_handling' => [
        'retryable_errors' => [
            'TIMEOUT',
            'CONNECTION_ERROR',
            'SERVER_ERROR',
            'TEMPORARY_UNAVAILABLE',
            'RATE_LIMITED',
        ],
        'permanent_errors' => [
            'INVALID_CREDENTIALS',
            'UNAUTHORIZED',
            'INVALID_DATA',
            'DUPLICATE_SUBMISSION',
            'SCHOOL_NOT_AUTHORIZED',
        ],
        'notification_threshold' => 5, // Send notification after 5 consecutive failures
    ],

    /*
    |--------------------------------------------------------------------------
    | Monitoring & Alerts
    |--------------------------------------------------------------------------
    */

    'monitoring' => [
        'success_rate_threshold' => 90, // Alert if success rate drops below 90%
        'failure_count_threshold' => 10, // Alert if more than 10 failures in 1 hour
        'alert_email' => env('STATE_INTEGRATION_ALERT_EMAIL'),
        'dashboard_refresh_interval' => 30, // seconds
    ],
];