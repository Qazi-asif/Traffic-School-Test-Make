<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    // Find all Florida courses
    $floridaCourses = DB::table('courses')->where('title', 'LIKE', '%Florida%')->get();

    echo 'Found '.count($floridaCourses)." Florida courses:\n";
    foreach ($floridaCourses as $course) {
        $chapterCount = DB::table('chapters')->where('course_id', $course->id)->count();
        echo "- ID {$course->id}: {$course->title} ({$chapterCount} chapters)\n";
    }

    // Keep only the course with most chapters (should be ID 5 with 40 chapters)
    $bestCourse = $floridaCourses->sortByDesc(function ($course) {
        return DB::table('chapters')->where('course_id', $course->id)->count();
    })->first();

    echo "\nKeeping course ID {$bestCourse->id} with most chapters\n";

    // Delete other Florida courses
    foreach ($floridaCourses as $course) {
        if ($course->id != $bestCourse->id) {
            DB::table('courses')->where('id', $course->id)->delete();
            echo "✓ Deleted duplicate course ID {$course->id}\n";
        }
    }

    echo "\n🎉 Cleanup complete! Only the complete Florida course remains.\n";

} catch (Exception $e) {
    echo '❌ Error: '.$e->getMessage()."\n";
}
