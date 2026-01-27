<?php

require 'vendor/autoload.php';

$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Adding Final Exam Question to Course 21 ===\n\n";

try {
    $courseId = 21;
    
    // Check current question count
    $currentCount = DB::table('final_exam_questions')->where('course_id', $courseId)->count();
    echo "Current final exam questions for course {$courseId}: {$currentCount}\n";
    
    if ($currentCount >= 25) {
        echo "✅ Course already has enough questions ({$currentCount})\n";
        exit;
    }
    
    // Get the highest order_index
    $maxOrder = DB::table('final_exam_questions')
        ->where('course_id', $courseId)
        ->max('order_index') ?: 0;
    
    // Add a new question
    $newQuestion = [
        'course_id' => $courseId,
        'question_text' => 'What is the most important factor in preventing traffic accidents?',
        'question_type' => 'multiple_choice',
        'options' => json_encode([
            'A' => 'Driving faster to get to your destination quickly',
            'B' => 'Staying alert and focused while driving',
            'C' => 'Using your phone to stay connected',
            'D' => 'Playing loud music to stay awake'
        ]),
        'correct_answer' => 'B',
        'explanation' => 'Staying alert and focused while driving is the most important factor in preventing traffic accidents. Distracted or inattentive driving is a leading cause of accidents.',
        'points' => 1,
        'order_index' => $maxOrder + 1,
        'created_at' => now(),
        'updated_at' => now(),
    ];
    
    $questionId = DB::table('final_exam_questions')->insertGetId($newQuestion);
    
    echo "✅ Added new final exam question (ID: {$questionId})\n";
    echo "   Question: {$newQuestion['question_text']}\n";
    echo "   Correct Answer: {$newQuestion['correct_answer']}\n";
    
    // Check new count
    $newCount = DB::table('final_exam_questions')->where('course_id', $courseId)->count();
    echo "\nNew total questions for course {$courseId}: {$newCount}\n";
    
    if ($newCount >= 25) {
        echo "✅ Course now has enough questions for final exam!\n";
    } else {
        echo "⚠️  Still need " . (25 - $newCount) . " more questions\n";
    }
    
    echo "\n=== Summary ===\n";
    echo "- Added 1 new final exam question\n";
    echo "- Course 21 now has {$newCount} final exam questions\n";
    echo "- Final exam should now work in course player\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}