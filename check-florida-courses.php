<?php

require_once 'vendor/autoload.php';

// Load Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Florida Courses in Database ===\n";

$courses = DB::table('florida_courses')->get(['id', 'title', 'course_type', 'state_code']);

if ($courses->isEmpty()) {
    echo "No courses found in florida_courses table.\n";
    
    // Check if seeder needs to be run
    echo "\nRunning Florida BDI Course Seeder...\n";
    
    try {
        $seeder = new \Database\Seeders\FloridaBDICourseSeeder();
        $seeder->run();
        echo "Seeder completed successfully!\n";
        
        // Check again
        $courses = DB::table('florida_courses')->get(['id', 'title', 'course_type', 'state_code']);
    } catch (Exception $e) {
        echo "Error running seeder: " . $e->getMessage() . "\n";
    }
}

foreach ($courses as $course) {
    echo "ID: {$course->id} | Type: {$course->course_type} | State: {$course->state_code}\n";
    echo "Title: {$course->title}\n";
    echo "---\n";
}

?>