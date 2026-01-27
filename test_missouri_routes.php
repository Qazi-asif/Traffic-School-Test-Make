<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Testing Missouri Form 4444 Routes ===\n";

// Test route registration
$router = app('router');
$routes = $router->getRoutes();

$missouriRoutes = [
    'GET|HEAD' => [
        'missouri/form4444/{formId}/download' => 'downloadForm4444',
        'missouri/user/{userId}/forms' => 'getUserForms',
        'missouri/submission-status/{userId}' => 'getSubmissionStatus',
        'admin/missouri-forms' => 'admin view',
        'api/missouri/forms/all' => 'getAllForms',
        'api/missouri/expiring-forms' => 'getExpiringForms',
    ],
    'POST' => [
        'missouri/form4444/generate' => 'generateForm4444',
        'missouri/form4444/{formId}/email' => 'emailForm4444',
        'missouri/form4444/{formId}/submit-dor' => 'submitToDOR',
    ]
];

foreach ($missouriRoutes as $method => $routeList) {
    echo "\n{$method} Routes:\n";
    foreach ($routeList as $uri => $action) {
        $found = false;
        foreach ($routes as $route) {
            if (in_array($method, explode('|', $route->methods()[0])) && 
                str_contains($route->uri(), str_replace('{formId}', '{', str_replace('{userId}', '{', $uri)))) {
                echo "✅ {$uri} -> {$action}\n";
                $found = true;
                break;
            }
        }
        if (!$found) {
            echo "❌ {$uri} -> {$action} - MISSING\n";
        }
    }
}

echo "\n=== Route Test Complete ===\n";
echo "If all routes show ✅, the Missouri Form 4444 routing is complete.\n";