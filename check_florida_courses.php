<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== CHECKING FLORIDA COURSES ===\n\n";

// Get all Florida courses
$floridaCourses = DB::table('florida_courses')->get();

echo "Total Florida courses: " . count($floridaCourses) . "\n\n";

foreach ($floridaCourses as $course) {
    echo "ID: {$course->id}\n";
    echo "Title: {$course->title}\n";
    echo "State: {$course->state_code}\n";
    echo "---\n";
}

// Also check regular courses table
echo "\n=== CHECKING REGULAR COURSES ===\n\n";
$regularCourses = DB::table('courses')->where('state_code', 'FL')->get();

echo "Total FL courses in 'courses' table: " . count($regularCourses) . "\n\n";

foreach ($regularCourses as $course) {
    echo "ID: {$course->id}\n";
    echo "Title: {$course->title}\n";
    echo "State: {$course->state_code}\n";
    echo "---\n";
}
