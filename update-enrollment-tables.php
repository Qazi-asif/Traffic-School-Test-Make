<?php

require_once 'vendor/autoload.php';

// Load Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Updating enrollment course_table values...\n";

$updated = 0;
$notFound = 0;

// Get all enrollments without course_table set
$enrollments = DB::table('user_course_enrollments')
    ->whereNull('course_table')
    ->orWhere('course_table', '')
    ->get();

echo "Found " . count($enrollments) . " enrollments to update\n";

foreach ($enrollments as $enrollment) {
    // Check if course exists in florida_courses
    $floridaCourse = DB::table('florida_courses')->where('id', $enrollment->course_id)->exists();
    $regularCourse = DB::table('courses')->where('id', $enrollment->course_id)->exists();
    
    if ($floridaCourse) {
        DB::table('user_course_enrollments')
            ->where('id', $enrollment->id)
            ->update(['course_table' => 'florida_courses']);
        echo "Updated enrollment {$enrollment->id} to florida_courses\n";
        $updated++;
    } elseif ($regularCourse) {
        DB::table('user_course_enrollments')
            ->where('id', $enrollment->id)
            ->update(['course_table' => 'courses']);
        echo "Updated enrollment {$enrollment->id} to courses\n";
        $updated++;
    } else {
        echo "Course not found for enrollment {$enrollment->id} (course_id: {$enrollment->course_id})\n";
        // Default to florida_courses for missing courses
        DB::table('user_course_enrollments')
            ->where('id', $enrollment->id)
            ->update(['course_table' => 'florida_courses']);
        echo "Defaulted enrollment {$enrollment->id} to florida_courses\n";
        $notFound++;
    }
}

echo "Migration complete! Updated: {$updated}, Not found (defaulted): {$notFound}\n";