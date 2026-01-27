<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    echo "=== Available Enrollments ===\n";
    
    // Get all enrollments
    $enrollments = \DB::table('user_course_enrollments')
        ->orderBy('id')
        ->get();
    
    if ($enrollments->isEmpty()) {
        echo "❌ No enrollments found\n";
        exit;
    }
    
    echo "✅ Found {$enrollments->count()} enrollments:\n\n";
    
    foreach ($enrollments as $enrollment) {
        echo "Enrollment ID: {$enrollment->id}\n";
        echo "- User ID: {$enrollment->user_id}\n";
        echo "- Course ID: {$enrollment->course_id}\n";
        echo "- Course Table: " . ($enrollment->course_table ?? 'courses') . "\n";
        echo "- Status: {$enrollment->status}\n";
        echo "- Payment Status: {$enrollment->payment_status}\n";
        
        // Get course title
        $courseTable = $enrollment->course_table ?? 'courses';
        $course = \DB::table($courseTable)->where('id', $enrollment->course_id)->first();
        
        if ($course) {
            echo "- Course Title: {$course->title}\n";
        } else {
            echo "- Course Title: NOT FOUND\n";
        }
        
        echo "\n";
    }
    
    echo "=== Available Florida Courses ===\n";
    
    $floridaCourses = \DB::table('florida_courses')->get();
    
    if ($floridaCourses->isEmpty()) {
        echo "❌ No Florida courses found\n";
    } else {
        echo "✅ Found {$floridaCourses->count()} Florida courses:\n\n";
        
        foreach ($floridaCourses as $course) {
            echo "Course ID: {$course->id}\n";
            echo "- Title: {$course->title}\n";
            echo "- State: " . ($course->state ?? 'N/A') . "\n";
            echo "- Price: $" . ($course->price ?? '0') . "\n";
            echo "\n";
        }
    }
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}