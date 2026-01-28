<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== CITATION NUMBER DIAGNOSTIC ===\n\n";

try {
    // Check enrollments
    $enrollments = \App\Models\UserCourseEnrollment::with('user')->get();
    
    echo "Found " . $enrollments->count() . " enrollments:\n\n";
    
    foreach ($enrollments as $enrollment) {
        echo "Enrollment ID: {$enrollment->id}\n";
        echo "User ID: {$enrollment->user_id}\n";
        echo "User Email: {$enrollment->user->email}\n";
        echo "Citation Number (enrollment): " . ($enrollment->citation_number ?? 'NULL') . "\n";
        echo "Citation Number (user): " . ($enrollment->user->citation_number ?? 'NULL') . "\n";
        echo "Court Selected (enrollment): " . ($enrollment->court_selected ?? 'NULL') . "\n";
        echo "Court Selected (user): " . ($enrollment->user->court_selected ?? 'NULL') . "\n";
        echo "Insurance Discount Only: " . ($enrollment->user->insurance_discount_only ? 'YES' : 'NO') . "\n";
        echo "---\n";
    }
    
    // Check failed state transmissions
    echo "\n=== FAILED STATE TRANSMISSIONS ===\n\n";
    
    $failedTransmissions = \App\Models\StateTransmission::where('status', 'error')
        ->where('response_message', 'like', '%Citation number is required%')
        ->with(['enrollment.user'])
        ->get();
    
    echo "Found " . $failedTransmissions->count() . " failed transmissions:\n\n";
    
    foreach ($failedTransmissions as $transmission) {
        echo "Transmission ID: {$transmission->id}\n";
        echo "Enrollment ID: {$transmission->enrollment_id}\n";
        echo "State: {$transmission->state}\n";
        echo "System: {$transmission->system}\n";
        echo "Error: {$transmission->response_message}\n";
        
        if ($transmission->enrollment) {
            echo "Enrollment Citation: " . ($transmission->enrollment->citation_number ?? 'NULL') . "\n";
            echo "User Citation: " . ($transmission->enrollment->user->citation_number ?? 'NULL') . "\n";
        }
        echo "---\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}