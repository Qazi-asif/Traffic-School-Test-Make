<?php
// Debug script to check quiz data structure
// Run this from the Laravel root directory: php debug_quiz_data.php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Quiz Data Debug ===\n\n";

// Get a sample enrollment with quiz data
try {
    $enrollment = DB::table('user_course_enrollments as e')
        ->leftJoin('users as u', 'e.user_id', '=', 'u.id')
        ->select('e.id', 'e.user_id', 'u.first_name', 'u.last_name', 'e.progress_percentage')
        ->where('e.progress_percentage', '>', 0)
        ->first();
    
    if ($enrollment) {
        echo "Sample enrollment: ID {$enrollment->id}, User: {$enrollment->first_name} {$enrollment->last_name}\n";
        echo "Progress: {$enrollment->progress_percentage}%\n\n";
        
        // Check chapter quiz results
        $quizResults = DB::table('chapter_quiz_results')
            ->where('user_id', $enrollment->user_id)
            ->get();
        
        echo "=== Chapter Quiz Results ===\n";
        echo "Found " . $quizResults->count() . " quiz results for this user\n\n";
        
        foreach ($quizResults as $result) {
            echo "Chapter {$result->chapter_id}: {$result->percentage}% ({$result->correct_answers}/{$result->total_questions})\n";
        }
        
        // Check quiz attempts
        echo "\n=== Quiz Attempts ===\n";
        $quizAttempts = DB::table('quiz_attempts')
            ->where('enrollment_id', $enrollment->id)
            ->get();
        
        echo "Found " . $quizAttempts->count() . " quiz attempts for this enrollment\n\n";
        
        foreach ($quizAttempts as $attempt) {
            echo "Chapter {$attempt->chapter_id}: Score {$attempt->score}%\n";
            echo "Questions attempted: " . substr($attempt->questions_attempted, 0, 100) . "...\n";
            
            // Try to decode the JSON
            $questionsData = json_decode($attempt->questions_attempted, true);
            if (is_array($questionsData)) {
                echo "Decoded " . count($questionsData) . " questions\n";
                if (count($questionsData) > 0) {
                    echo "Sample question data structure:\n";
                    $sample = $questionsData[0];
                    foreach ($sample as $key => $value) {
                        echo "  - {$key}: " . (is_string($value) ? $value : json_encode($value)) . "\n";
                    }
                }
            } else {
                echo "Could not decode questions JSON\n";
            }
            echo "\n";
        }
        
        // Check questions table
        echo "=== Questions Table Sample ===\n";
        $questions = DB::table('questions')->limit(3)->get();
        if ($questions->count() > 0) {
            $sample = $questions->first();
            echo "Sample question structure:\n";
            foreach ($sample as $key => $value) {
                echo "  - {$key}: " . (strlen($value) > 50 ? substr($value, 0, 50) . '...' : $value) . "\n";
            }
        } else {
            echo "No questions found in questions table\n";
        }
        
    } else {
        echo "No enrollments found with progress > 0\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\nDebug complete!\n";
?>