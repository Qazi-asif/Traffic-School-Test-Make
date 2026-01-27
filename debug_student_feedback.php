<?php
// Debug script to check student feedback data structure
// Run this from the Laravel root directory: php debug_student_feedback.php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Student Feedback Debug ===\n\n";

// Check if tables exist
$tables = ['user_course_enrollments', 'chapter_quiz_results', 'chapters', 'users'];

foreach ($tables as $table) {
    try {
        $count = DB::table($table)->count();
        echo "✅ Table '{$table}' exists with {$count} records\n";
    } catch (Exception $e) {
        echo "❌ Table '{$table}' error: " . $e->getMessage() . "\n";
    }
}

echo "\n=== Sample Data ===\n";

// Get a sample enrollment
try {
    $enrollment = DB::table('user_course_enrollments as e')
        ->leftJoin('users as u', 'e.user_id', '=', 'u.id')
        ->select('e.id', 'e.user_id', 'e.course_id', 'u.first_name', 'u.last_name', 'e.progress_percentage')
        ->where('e.progress_percentage', '>', 0)
        ->first();
    
    if ($enrollment) {
        echo "Sample enrollment: ID {$enrollment->id}, User: {$enrollment->first_name} {$enrollment->last_name}\n";
        echo "Progress: {$enrollment->progress_percentage}%\n";
        
        // Check chapter quiz results for this user
        $quizResults = DB::table('chapter_quiz_results')
            ->where('user_id', $enrollment->user_id)
            ->get();
        
        echo "Quiz results for this user: " . $quizResults->count() . " records\n";
        
        if ($quizResults->count() > 0) {
            $sample = $quizResults->first();
            echo "Sample quiz result structure:\n";
            foreach ($sample as $key => $value) {
                echo "  - {$key}: {$value}\n";
            }
        }
        
    } else {
        echo "No enrollments found with progress > 0\n";
    }
} catch (Exception $e) {
    echo "Error getting sample data: " . $e->getMessage() . "\n";
}

echo "\n=== Chapter Quiz Results Structure ===\n";
try {
    $columns = DB::select("DESCRIBE chapter_quiz_results");
    foreach ($columns as $column) {
        echo "  - {$column->Field} ({$column->Type})\n";
    }
} catch (Exception $e) {
    echo "Error getting table structure: " . $e->getMessage() . "\n";
}

echo "\nDebug complete!\n";
?>