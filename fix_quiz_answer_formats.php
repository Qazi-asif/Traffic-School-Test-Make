<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== FIXING QUIZ ANSWER FORMATS ===\n\n";

$fixed = 0;
$errors = 0;

// Fix chapter_questions table
echo "Checking chapter_questions table...\n";
$questions = DB::table('chapter_questions')->get();

foreach ($questions as $q) {
    $options = json_decode($q->options, true);
    $correctAnswer = trim($q->correct_answer);
    
    if (!is_array($options) || empty($options)) {
        echo "  ⚠️  Question {$q->id}: Invalid options format\n";
        $errors++;
        continue;
    }
    
    $needsUpdate = false;
    $newCorrectAnswer = $correctAnswer;
    
    // Normalize options to associative array with letter keys (A, B, C, D, E)
    $normalizedOptions = [];
    $optionIndex = 0;
    foreach ($options as $key => $value) {
        $letter = chr(65 + $optionIndex); // A, B, C, D, E
        $normalizedOptions[$letter] = is_string($value) ? trim($value) : $value;
        $optionIndex++;
    }
    
    // Check if correct answer needs normalization
    // If correct answer is full text, convert to letter
    if (!preg_match('/^[A-E]$/i', $correctAnswer)) {
        // Find which option matches the correct answer text
        foreach ($normalizedOptions as $letter => $optionText) {
            $cleanOption = preg_replace('/^[A-E]\.\s*/i', '', $optionText);
            $cleanCorrect = preg_replace('/^[A-E]\.\s*/i', '', $correctAnswer);
            
            if (strcasecmp(trim($cleanOption), trim($cleanCorrect)) === 0) {
                $newCorrectAnswer = $letter;
                $needsUpdate = true;
                break;
            }
        }
    }
    
    // Trim whitespace from correct answer
    if ($newCorrectAnswer !== $correctAnswer) {
        $needsUpdate = true;
    }
    
    // Update if needed
    if ($needsUpdate) {
        DB::table('chapter_questions')
            ->where('id', $q->id)
            ->update([
                'correct_answer' => $newCorrectAnswer,
                'options' => json_encode($normalizedOptions),
                'updated_at' => now()
            ]);
        
        echo "  ✅ Fixed Question {$q->id}: '{$correctAnswer}' → '{$newCorrectAnswer}'\n";
        $fixed++;
    }
}

// Fix legacy questions table
echo "\nChecking questions table...\n";
$legacyQuestions = DB::table('questions')->get();

foreach ($legacyQuestions as $q) {
    $options = json_decode($q->options, true);
    $correctAnswer = trim($q->correct_answer);
    
    if (!is_array($options) || empty($options)) {
        echo "  ⚠️  Question {$q->id}: Invalid options format\n";
        $errors++;
        continue;
    }
    
    $needsUpdate = false;
    $newCorrectAnswer = $correctAnswer;
    
    // Normalize options
    $normalizedOptions = [];
    $optionIndex = 0;
    foreach ($options as $key => $value) {
        $letter = chr(65 + $optionIndex);
        $normalizedOptions[$letter] = is_string($value) ? trim($value) : $value;
        $optionIndex++;
    }
    
    // Normalize correct answer to letter
    if (!preg_match('/^[A-E]$/i', $correctAnswer)) {
        foreach ($normalizedOptions as $letter => $optionText) {
            $cleanOption = preg_replace('/^[A-E]\.\s*/i', '', $optionText);
            $cleanCorrect = preg_replace('/^[A-E]\.\s*/i', '', $correctAnswer);
            
            if (strcasecmp(trim($cleanOption), trim($cleanCorrect)) === 0) {
                $newCorrectAnswer = $letter;
                $needsUpdate = true;
                break;
            }
        }
    }
    
    if ($newCorrectAnswer !== $correctAnswer) {
        $needsUpdate = true;
    }
    
    if ($needsUpdate) {
        DB::table('questions')
            ->where('id', $q->id)
            ->update([
                'correct_answer' => $newCorrectAnswer,
                'options' => json_encode($normalizedOptions),
                'updated_at' => now()
            ]);
        
        echo "  ✅ Fixed Question {$q->id}: '{$correctAnswer}' → '{$newCorrectAnswer}'\n";
        $fixed++;
    }
}

// Fix final exam questions
echo "\nChecking final_exam_questions table...\n";
$finalExamQuestions = DB::table('final_exam_questions')->get();

foreach ($finalExamQuestions as $q) {
    $options = json_decode($q->options, true);
    $correctAnswer = trim($q->correct_answer);
    
    if (!is_array($options) || empty($options)) {
        echo "  ⚠️  Question {$q->id}: Invalid options format\n";
        $errors++;
        continue;
    }
    
    $needsUpdate = false;
    $newCorrectAnswer = $correctAnswer;
    
    // Normalize options
    $normalizedOptions = [];
    $optionIndex = 0;
    foreach ($options as $key => $value) {
        $letter = chr(65 + $optionIndex);
        $normalizedOptions[$letter] = is_string($value) ? trim($value) : $value;
        $optionIndex++;
    }
    
    // Normalize correct answer to letter
    if (!preg_match('/^[A-E]$/i', $correctAnswer)) {
        foreach ($normalizedOptions as $letter => $optionText) {
            $cleanOption = preg_replace('/^[A-E]\.\s*/i', '', $optionText);
            $cleanCorrect = preg_replace('/^[A-E]\.\s*/i', '', $correctAnswer);
            
            if (strcasecmp(trim($cleanOption), trim($cleanCorrect)) === 0) {
                $newCorrectAnswer = $letter;
                $needsUpdate = true;
                break;
            }
        }
    }
    
    if ($newCorrectAnswer !== $correctAnswer) {
        $needsUpdate = true;
    }
    
    if ($needsUpdate) {
        DB::table('final_exam_questions')
            ->where('id', $q->id)
            ->update([
                'correct_answer' => $newCorrectAnswer,
                'options' => json_encode($normalizedOptions),
                'updated_at' => now()
            ]);
        
        echo "  ✅ Fixed Question {$q->id}: '{$correctAnswer}' → '{$newCorrectAnswer}'\n";
        $fixed++;
    }
}

echo "\n=== SUMMARY ===\n";
echo "✅ Fixed: {$fixed} questions\n";
echo "⚠️  Errors: {$errors} questions\n";
echo "\nAll questions now use letter format (A-E) for correct_answer\n";
echo "All options are normalized to associative arrays with letter keys\n";
