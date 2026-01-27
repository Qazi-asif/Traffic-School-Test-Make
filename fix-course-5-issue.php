<?php

require 'vendor/autoload.php';

$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Fixing Course 5 Issues ===\n\n";

try {
    // Get the regular course 5
    $regularCourse = DB::table('courses')->where('id', 5)->first();
    echo "Regular Course 5: {$regularCourse->title}\n";
    
    // Check if there's already a florida course with the same title
    $existingFloridaCourse = DB::table('florida_courses')
        ->where('title', 'LIKE', '%Texas%')
        ->orWhere('title', 'LIKE', '%6 Hour%')
        ->get();
    
    echo "\nExisting Florida courses with similar titles:\n";
    foreach ($existingFloridaCourse as $course) {
        echo "- ID {$course->id}: {$course->title}\n";
    }
    
    // Check chapters that reference course_id 5
    $chaptersForCourse5 = DB::table('chapters')->where('course_id', 5)->get();
    echo "\nChapters directly referencing course_id 5: " . $chaptersForCourse5->count() . "\n";
    
    // Check if there are chapters with course_table = 'florida_courses' and course_id = 5
    $floridaChaptersForCourse5 = DB::table('chapters')
        ->where('course_id', 5)
        ->where('course_table', 'florida_courses')
        ->get();
    echo "Florida chapters for course_id 5: " . $floridaChaptersForCourse5->count() . "\n";
    
    // From our previous fix, we know course 5 had 30 chapters. Let's find them.
    echo "\nSearching for Texas 6 Hour course chapters...\n";
    $texasChapters = DB::table('chapters')
        ->where('title', 'LIKE', '%Texas%')
        ->orWhere('title', 'LIKE', '%DANGERS OF CITY DRIVING%')
        ->orWhere('title', 'LIKE', '%Course Introduction%')
        ->get();
    
    echo "Found " . $texasChapters->count() . " potential Texas course chapters:\n";
    foreach ($texasChapters->take(5) as $chapter) {
        echo "- Chapter {$chapter->id}: '{$chapter->title}' (Course: {$chapter->course_id}, Table: {$chapter->course_table})\n";
    }
    
    // Check if course 5 should be moved to florida_courses or if there's a duplicate
    echo "\n=== SOLUTION OPTIONS ===\n";
    
    // Option 1: Move course 5 to florida_courses
    echo "Option 1: Move course 5 from 'courses' to 'florida_courses'\n";
    
    // Option 2: Find the correct Florida course ID
    $correctFloridaCourse = DB::table('florida_courses')
        ->where('title', 'LIKE', '%Texas%')
        ->where('title', 'LIKE', '%6 Hour%')
        ->first();
    
    if ($correctFloridaCourse) {
        echo "Option 2: Use existing Florida course ID {$correctFloridaCourse->id}: '{$correctFloridaCourse->title}'\n";
        
        // Check if chapters are already associated with the correct course
        $correctChapters = DB::table('chapters')
            ->where('course_id', $correctFloridaCourse->id)
            ->where('course_table', 'florida_courses')
            ->count();
        echo "  - This course already has {$correctChapters} chapters\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}