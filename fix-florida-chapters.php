<?php

require 'vendor/autoload.php';

$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Florida Chapters Fix Script ===\n\n";

try {
    // Get all Florida course IDs
    $floridaCourseIds = DB::table('florida_courses')->pluck('id')->toArray();
    
    echo "Found " . count($floridaCourseIds) . " Florida courses: " . implode(', ', $floridaCourseIds) . "\n\n";
    
    // Check chapters that belong to Florida courses but have wrong course_table
    $wrongChapters = DB::table('chapters')
        ->whereIn('course_id', $floridaCourseIds)
        ->where('course_table', '!=', 'florida_courses')
        ->get();
    
    echo "Found " . $wrongChapters->count() . " chapters with incorrect course_table:\n";
    
    foreach ($wrongChapters as $chapter) {
        echo "- Chapter {$chapter->id}: '{$chapter->title}' (Course: {$chapter->course_id}, Current table: {$chapter->course_table})\n";
    }
    
    if ($wrongChapters->count() > 0) {
        echo "\nFixing course_table values...\n";
        
        $updated = DB::table('chapters')
            ->whereIn('course_id', $floridaCourseIds)
            ->where('course_table', '!=', 'florida_courses')
            ->update(['course_table' => 'florida_courses']);
        
        echo "Updated {$updated} chapters to use course_table = 'florida_courses'\n";
    } else {
        echo "\nNo chapters need fixing!\n";
    }
    
    // Show summary for each Florida course
    echo "\n=== Summary by Florida Course ===\n";
    
    foreach ($floridaCourseIds as $courseId) {
        $course = DB::table('florida_courses')->where('id', $courseId)->first();
        $chapterCount = DB::table('chapters')
            ->where('course_id', $courseId)
            ->where('course_table', 'florida_courses')
            ->count();
        
        echo "Course {$courseId}: '{$course->title}' - {$chapterCount} chapters\n";
        
        if ($chapterCount > 0) {
            $chapters = DB::table('chapters')
                ->where('course_id', $courseId)
                ->where('course_table', 'florida_courses')
                ->orderBy('order_index')
                ->get(['id', 'title', 'order_index', 'is_active']);
            
            foreach ($chapters as $chapter) {
                $status = $chapter->is_active ? 'Active' : 'Inactive';
                echo "  - Chapter {$chapter->id}: '{$chapter->title}' (Order: {$chapter->order_index}, {$status})\n";
            }
        }
        echo "\n";
    }
    
    echo "=== Fix completed successfully! ===\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}