<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Checking questions for Chapter 39...\n\n";

// Check if chapter 39 exists
$chapter = DB::table('course_chapters')->where('id', 39)->first();
if ($chapter) {
    echo "Chapter 39: {$chapter->title} (Course ID: {$chapter->course_id})\n";
    
    $course = DB::table('florida_courses')->where('id', $chapter->course_id)->first();
    if ($course) {
        echo "Course: {$course->title} (State: {$course->state_code})\n";
    }
} else {
    echo "Chapter 39 not found!\n";
    exit;
}

echo "\n=== QUESTIONS IN CHAPTER_QUESTIONS TABLE ===\n";
$questions = DB::table('chapter_questions')->where('chapter_id', 39)->get();

if ($questions->count() > 0) {
    echo "Found {$questions->count()} questions for chapter 39:\n";
    foreach ($questions as $q) {
        $quizSet = $q->quiz_set ?? 'NULL';
        echo "- ID {$q->id}: {$q->question_text} (Quiz Set: {$quizSet})\n";
    }
} else {
    echo "❌ No questions found for chapter 39 in chapter_questions table\n";
    echo "This is why the question manager shows 'No questions yet'\n";
}

echo "\n=== QUESTIONS IN OTHER TABLES ===\n";
// Check if there are questions in the old questions table
$oldQuestions = DB::table('questions')->where('chapter_id', 39)->get();
if ($oldQuestions->count() > 0) {
    echo "Found {$oldQuestions->count()} questions in old 'questions' table:\n";
    foreach ($oldQuestions as $q) {
        echo "- ID {$q->id}: {$q->question_text}\n";
    }
} else {
    echo "No questions in old 'questions' table for chapter 39\n";
}

echo "\n=== SOLUTION ===\n";
echo "To see rotating quiz options:\n";
echo "1. Go to: http://127.0.0.1:8000/admin/chapters/39/questions\n";
echo "2. You should see 'Quiz Set 1' and 'Quiz Set 2' toggle buttons\n";
echo "3. Click 'Add Question' to create questions for Quiz Set 1\n";
echo "4. Switch to Quiz Set 2 and add different questions\n";
echo "5. Questions will be saved with quiz_set = 1 or 2\n";