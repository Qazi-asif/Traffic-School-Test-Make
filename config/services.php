<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
    ],

    'paypal' => [
        'client_id' => env('PAYPAL_CLIENT_ID'),
        'client_secret' => env('PAYPAL_CLIENT_SECRET'),
        'mode' => env('PAYPAL_MODE', 'sandbox'), // sandbox or live
    ],

    'florida_dicds' => [
        'wsdl' => env('FLORIDA_DICDS_WSDL', 'storage/wsdl/florida-dicds.wsdl'),
        'username' => env('FLORIDA_USERNAME'),
        'password' => env('FLORIDA_PASSWORD'),
        'timeout' => env('FLORIDA_API_TIMEOUT', 60),
    ],

    'florida' => [
        'wsdl_url' => env('FLHSMV_WSDL_URL', 'https://services.flhsmv.gov/DriverSchoolWebService/wsPrimerComponentService.svc?wsdl'),
        'service_url' => env('FLHSMV_SERVICE_URL', 'https://services.flhsmv.gov/DriverSchoolWebService/wsPrimerComponentService.svc'),
        'username' => env('FLHSMV_USERNAME', 'NMNSEdits'),
        'password' => env('FLHSMV_PASSWORD', 'LoveFL2025!'),
        'school_id' => env('FLHSMV_DEFAULT_SCHOOL_ID', '30981'),
        'instructor_id' => env('FLHSMV_DEFAULT_INSTRUCTOR_ID', '76397'),
        'course_id' => env('FLHSMV_DEFAULT_COURSE_ID', '40585'),
        'timeout' => env('FLHSMV_TIMEOUT', 30),
        'environment' => env('FLHSMV_ENVIRONMENT', 'production'),
    ],

];
