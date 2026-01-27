<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    echo "=== Checking Final Exam Questions ===\n";
    
    // Find Florida 4-Hour BDI Course
    $floridaCourse = \DB::table('florida_courses')
        ->where('title', 'LIKE', '%4%Hour%')
        ->orWhere('title', 'LIKE', '%BDI%')
        ->first();
    
    if (!$floridaCourse) {
        echo "❌ Florida 4-Hour BDI Course not found\n";
        exit;
    }
    
    echo "✅ Found Florida course: {$floridaCourse->title} (ID: {$floridaCourse->id})\n";
    
    // Check final exam questions
    $questions = \DB::table('final_exam_questions')
        ->where('course_id', $floridaCourse->id)
        ->orderBy('id')
        ->limit(5)
        ->get();
    
    if ($questions->isEmpty()) {
        echo "❌ No final exam questions found\n";
        exit;
    }
    
    echo "\n=== Sample Questions (showing numbering issue) ===\n";
    foreach ($questions as $question) {
        echo "Question ID: {$question->id}\n";
        echo "Question Text: " . substr($question->question_text, 0, 100) . "...\n";
        echo "---\n";
    }
    
    // Check if questions have numbers at the beginning
    $questionsWithNumbers = \DB::table('final_exam_questions')
        ->where('course_id', $floridaCourse->id)
        ->where('question_text', 'REGEXP', '^[0-9]+[\\.\\)]+')
        ->count();
    
    $totalQuestions = \DB::table('final_exam_questions')
        ->where('course_id', $floridaCourse->id)
        ->count();
    
    echo "\n=== Analysis ===\n";
    echo "Total questions: {$totalQuestions}\n";
    echo "Questions with numbers: {$questionsWithNumbers}\n";
    
    if ($questionsWithNumbers > 0) {
        echo "❌ ISSUE FOUND: Questions contain original numbering in question_text\n";
        echo "✅ SOLUTION: Remove numbers from question_text field\n";
    } else {
        echo "✅ No numbering issues found\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}