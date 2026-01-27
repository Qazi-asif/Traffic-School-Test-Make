<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Payment Gateway Configuration
    |--------------------------------------------------------------------------
    */

    'default_gateway' => env('PAYMENT_DEFAULT_GATEWAY', 'stripe'),

    'stripe' => [
        'public_key' => env('STRIPE_PUBLIC_KEY', ''),
        'secret_key' => env('STRIPE_SECRET_KEY', ''),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET', ''),
        'currency' => env('STRIPE_CURRENCY', 'usd'),
    ],

    'paypal' => [
        'client_id' => env('PAYPAL_CLIENT_ID', ''),
        'client_secret' => env('PAYPAL_CLIENT_SECRET', ''),
        'mode' => env('PAYPAL_MODE', 'sandbox'), // 'sandbox' or 'live'
        'currency' => env('PAYPAL_CURRENCY', 'USD'),
    ],

    'authorizenet' => [
        'login_id' => env('AUTHORIZENET_LOGIN_ID', ''),
        'transaction_key' => env('AUTHORIZENET_TRANSACTION_KEY', ''),
        'mode' => env('AUTHORIZENET_MODE', 'sandbox'), // 'sandbox' or 'production'
        'currency' => env('AUTHORIZENET_CURRENCY', 'USD'),
    ],

    'course_prices' => [
        'bdi_4hour' => env('COURSE_PRICE_BDI_4HOUR', 29.99),
        'adi_4hour' => env('COURSE_PRICE_ADI_4HOUR', 29.99),
        'tlsae_4hour' => env('COURSE_PRICE_TLSAE_4HOUR', 29.99),
    ],
];
