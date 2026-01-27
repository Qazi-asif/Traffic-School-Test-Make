<?php

require 'vendor/autoload.php';

$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Checking Course 5 ===\n\n";

try {
    // Check florida_courses table
    $floridaCourse = DB::table('florida_courses')->where('id', 5)->first();
    echo "Florida courses table: " . ($floridaCourse ? 'EXISTS' : 'NOT FOUND') . "\n";
    if ($floridaCourse) {
        echo "Florida Course 5: {$floridaCourse->title}\n";
    }
    
    // Check regular courses table
    $regularCourse = DB::table('courses')->where('id', 5)->first();
    echo "Regular courses table: " . ($regularCourse ? 'EXISTS' : 'NOT FOUND') . "\n";
    if ($regularCourse) {
        echo "Regular Course 5: {$regularCourse->title}\n";
    }
    
    // Check chapters for course 5
    $chapters = DB::table('chapters')->where('course_id', 5)->get();
    echo "\nChapters for course 5: " . $chapters->count() . "\n";
    
    if ($chapters->count() > 0) {
        echo "Chapter details:\n";
        foreach ($chapters as $chapter) {
            echo "- Chapter {$chapter->id}: '{$chapter->title}' (Table: {$chapter->course_table}, Order: {$chapter->order_index})\n";
        }
    }
    
    // Check if course 5 should be in florida_courses based on our previous fix
    $shouldBeFloridaCourse = in_array(5, [18, 1, 4, 5, 7, 8, 9, 15, 17, 21, 22]);
    echo "\nShould be in florida_courses: " . ($shouldBeFloridaCourse ? 'YES' : 'NO') . "\n";
    
    if ($shouldBeFloridaCourse && !$floridaCourse && $regularCourse) {
        echo "\n*** ISSUE FOUND ***\n";
        echo "Course 5 should be in florida_courses but only exists in regular courses table!\n";
        echo "This explains the deletion error.\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}