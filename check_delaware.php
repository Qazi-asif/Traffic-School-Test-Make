<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Checking for Delaware courses and state codes...\n\n";

// Check all unique state codes in florida_courses
$stateCodes = DB::table('florida_courses')
    ->select('state_code')
    ->distinct()
    ->get();

echo "State codes in florida_courses table:\n";
foreach ($stateCodes as $state) {
    $count = DB::table('florida_courses')->where('state_code', $state->state_code)->count();
    echo "- {$state->state_code}: {$count} courses\n";
}

// Check all unique states in courses table
$states = DB::table('courses')
    ->select('state')
    ->distinct()
    ->get();

echo "\nStates in courses table:\n";
foreach ($states as $state) {
    $count = DB::table('courses')->where('state', $state->state)->count();
    echo "- {$state->state}: {$count} courses\n";
}

// Check for any Delaware references
$delawareCount = DB::table('florida_courses')
    ->where('state_code', 'DE')
    ->orWhere('title', 'LIKE', '%Delaware%')
    ->count();

echo "\nDelaware courses found: {$delawareCount}\n";

if ($delawareCount == 0) {
    echo "\nNo Delaware courses found. Need to create Delaware courses first.\n";
    
    // Show a sample course structure
    $sampleCourse = DB::table('florida_courses')->first();
    if ($sampleCourse) {
        echo "\nSample course structure:\n";
        foreach ((array)$sampleCourse as $key => $value) {
            echo "- {$key}: " . (is_string($value) ? substr($value, 0, 50) : $value) . "\n";
        }
    }
}