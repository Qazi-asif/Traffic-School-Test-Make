<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    // Copy Florida course to main courses table
    $floridaCourse = DB::table('florida_courses')->first();

    if ($floridaCourse) {
        // Delete existing Florida course in main table
        DB::table('courses')->where('title', 'LIKE', '%Florida%Defensive%')->delete();

        // Insert Florida course into main courses table
        $courseId = DB::table('courses')->insertGetId([
            'title' => $floridaCourse->title,
            'description' => $floridaCourse->description,
            'state' => $floridaCourse->state,
            'duration' => $floridaCourse->duration,
            'price' => $floridaCourse->price,
            'passing_score' => $floridaCourse->passing_score,
            'course_type' => $floridaCourse->course_type,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        echo "✓ Florida course copied to main courses table (ID: $courseId)\n";

        // Copy all chapters for the Florida course from main chapters table
        $floridaChapters = DB::table('chapters')->where('course_id', $floridaCourse->id)->get();

        foreach ($floridaChapters as $chapter) {
            DB::table('chapters')->where('id', $chapter->id)->update([
                'course_id' => $courseId,
                'updated_at' => now(),
            ]);
        }

        echo '✓ '.count($floridaChapters)." chapters linked to new Florida course (ID: $courseId)\n";
        echo "🎉 Florida course now available in course player!\n";

    } else {
        echo "❌ No Florida course found in florida_courses table\n";
    }

} catch (Exception $e) {
    echo '❌ Error: '.$e->getMessage()."\n";
}
