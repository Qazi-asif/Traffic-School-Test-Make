<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    echo "=== Fixing Final Exam Question Numbering ===\n";
    
    // Find all final exam questions with numbers at the beginning
    $questionsWithNumbers = \DB::table('final_exam_questions')
        ->where('question_text', 'REGEXP', '^[0-9]+[\\.\\)]+')
        ->get();
    
    if ($questionsWithNumbers->isEmpty()) {
        echo "✅ No questions with numbering issues found\n";
        exit;
    }
    
    echo "Found {$questionsWithNumbers->count()} questions with numbering issues\n";
    echo "Fixing questions...\n\n";
    
    $fixed = 0;
    
    foreach ($questionsWithNumbers as $question) {
        // Remove the number and period from the beginning of the question
        $originalText = $question->question_text;
        
        // Pattern to match various numbering formats:
        // - "229.) " 
        // - "237) "
        // - "20. "
        // - "250.) "
        $cleanedText = preg_replace('/^[0-9]+[\.\)]+\s*/', '', $originalText);
        
        // Trim any extra whitespace
        $cleanedText = trim($cleanedText);
        
        if ($cleanedText !== $originalText && !empty($cleanedText)) {
            \DB::table('final_exam_questions')
                ->where('id', $question->id)
                ->update(['question_text' => $cleanedText]);
            
            echo "✅ Fixed Question ID {$question->id}\n";
            echo "   Before: " . substr($originalText, 0, 80) . "...\n";
            echo "   After:  " . substr($cleanedText, 0, 80) . "...\n\n";
            
            $fixed++;
        }
    }
    
    echo "=== Fix Complete ===\n";
    echo "✅ Fixed {$fixed} questions\n";
    echo "The final exam should now show proper sequential numbering (1, 2, 3, etc.)\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}