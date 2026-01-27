<?php

return [
    'from_name' => env('NEWSLETTER_FROM_NAME', 'Traffic School'),
    'from_email' => env('NEWSLETTER_FROM_EMAIL', 'newsletter@trafficschool.com'),
    'double_optin' => env('NEWSLETTER_DOUBLE_OPTIN', false),
    'batch_size' => env('NEWSLETTER_BATCH_SIZE', 100),
    'rate_limit' => env('NEWSLETTER_RATE_LIMIT', 50), // per minute
    'bounce_threshold' => env('NEWSLETTER_BOUNCE_THRESHOLD', 3),
    'tracking_enabled' => env('NEWSLETTER_TRACKING', true),
    'unconfirmed_days' => 7, // Days before deleting unconfirmed
];
