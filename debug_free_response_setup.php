<?php
// Debug script to check free response quiz setup
require_once 'vendor/autoload.php';

// Load Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== FREE RESPONSE QUIZ DEBUG ===\n\n";

// Check if tables exist
$tables = ['free_response_quiz_placements', 'free_response_questions', 'user_quiz_question_selections'];
foreach ($tables as $table) {
    try {
        $count = \DB::table($table)->count();
        echo "✅ Table '{$table}': {$count} records\n";
    } catch (\Exception $e) {
        echo "❌ Table '{$table}': ERROR - {$e->getMessage()}\n";
    }
}

echo "\n=== PLACEMENTS ===\n";
$placements = \DB::table('free_response_quiz_placements')->get();
foreach ($placements as $placement) {
    echo "ID: {$placement->id}\n";
    echo "Course ID: {$placement->course_id}\n";
    echo "Title: {$placement->quiz_title}\n";
    echo "Random Selection: " . ($placement->use_random_selection ? 'YES' : 'NO') . "\n";
    if ($placement->use_random_selection) {
        echo "Questions to Select: {$placement->questions_to_select}\n";
    }
    echo "Active: " . ($placement->is_active ? 'YES' : 'NO') . "\n";
    echo "---\n";
}

echo "\n=== QUESTIONS ===\n";
$questions = \DB::table('free_response_questions')->get();
foreach ($questions as $question) {
    echo "ID: {$question->id}\n";
    echo "Course ID: {$question->course_id}\n";
    echo "Placement ID: {$question->placement_id}\n";
    echo "Question: " . substr($question->question_text, 0, 50) . "...\n";
    echo "Active: " . ($question->is_active ? 'YES' : 'NO') . "\n";
    echo "---\n";
}

echo "\n=== COURSE PLAYER TEST ===\n";
// Test what the course player would see
$courseId = 17; // Your course ID
echo "Testing course ID: {$courseId}\n";

$courseQuestions = \DB::table('free_response_questions')
    ->where('course_id', $courseId)
    ->where('is_active', true)
    ->get();

echo "Questions for course {$courseId}: " . $courseQuestions->count() . "\n";

$coursePlacements = \DB::table('free_response_quiz_placements')
    ->where('course_id', $courseId)
    ->where('is_active', true)
    ->get();

echo "Placements for course {$courseId}: " . $coursePlacements->count() . "\n";

if ($coursePlacements->count() > 0) {
    foreach ($coursePlacements as $placement) {
        $placementQuestions = \DB::table('free_response_questions')
            ->where('course_id', $courseId)
            ->where('placement_id', $placement->id)
            ->where('is_active', true)
            ->get();
        
        echo "Placement '{$placement->quiz_title}' has {$placementQuestions->count()} questions\n";
    }
}
?>