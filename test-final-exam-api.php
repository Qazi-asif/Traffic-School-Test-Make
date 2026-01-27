<?php

require 'vendor/autoload.php';

$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Testing Final Exam API ===\n\n";

try {
    $enrollmentId = 29;
    
    // Test the count API
    echo "Testing /api/final-exam/count?enrollment_id={$enrollmentId}\n";
    
    $enrollment = DB::table('user_course_enrollments')->where('id', $enrollmentId)->first();
    $courseId = $enrollment ? $enrollment->course_id : 1;
    
    $count = DB::table('final_exam_questions')
        ->where('course_id', $courseId)
        ->count();
    
    echo "- Enrollment {$enrollmentId} -> Course {$courseId}\n";
    echo "- Final exam questions available: {$count}\n";
    
    if ($count >= 25) {
        echo "✅ Enough questions available\n";
    } else {
        echo "❌ Not enough questions (need 25, have {$count})\n";
    }
    
    // Test the random questions API
    echo "\nTesting /api/final-exam/random/25?enrollment_id={$enrollmentId}\n";
    
    $questions = DB::table('final_exam_questions')
        ->where('course_id', $courseId)
        ->inRandomOrder()
        ->limit(25)
        ->get();
    
    echo "- Questions returned: " . $questions->count() . "\n";
    
    if ($questions->count() >= 25) {
        echo "✅ API would return 25 questions\n";
    } else {
        echo "❌ API would return only " . $questions->count() . " questions\n";
    }
    
    // Check if there are any issues with the questions themselves
    $invalidQuestions = DB::table('final_exam_questions')
        ->where('course_id', $courseId)
        ->where(function($query) {
            $query->whereNull('question_text')
                  ->orWhere('question_text', '')
                  ->orWhereNull('options')
                  ->orWhere('options', '')
                  ->orWhere('options', '[]')
                  ->orWhereNull('correct_answer')
                  ->orWhere('correct_answer', '');
        })
        ->count();
    
    echo "- Invalid/incomplete questions: {$invalidQuestions}\n";
    
    if ($invalidQuestions > 0) {
        echo "⚠️  Some questions may be incomplete\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}