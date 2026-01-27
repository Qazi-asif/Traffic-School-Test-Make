<?php
// Debug script to check what's actually stored in the database
require_once 'vendor/autoload.php';

// Load Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Get a specific chapter content to see what's stored
$chapterId = 17; // Change this to a chapter ID that has the numbering issue

$chapter = \App\Models\Chapter::find($chapterId);

if ($chapter) {
    echo "Chapter ID: " . $chapter->id . "\n";
    echo "Chapter Title: " . $chapter->title . "\n";
    echo "Course ID: " . $chapter->course_id . "\n";
    echo "\n=== RAW CONTENT ===\n";
    echo $chapter->content;
    echo "\n\n=== FORMATTED FOR DISPLAY ===\n";
    echo htmlspecialchars($chapter->content);
    echo "\n\n";
} else {
    echo "Chapter not found. Available chapters:\n";
    $chapters = \App\Models\Chapter::select('id', 'title', 'course_id')->take(10)->get();
    foreach ($chapters as $ch) {
        echo "ID: {$ch->id}, Title: {$ch->title}, Course: {$ch->course_id}\n";
    }
}
?>