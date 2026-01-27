<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== DIAGNOSING BROKEN QUIZZES ===\n\n";

function checkQuestion($q, $tableName) {
    $issues = [];
    $options = json_decode($q->options, true);
    $correctAnswer = $q->correct_answer;
    
    // Issue 1: Options not an array
    if (!is_array($options)) {
        $issues[] = "Options is not an array (type: " . gettype($options) . ")";
        return $issues;
    }
    
    // Issue 2: Empty options
    if (empty($options)) {
        $issues[] = "Options array is empty";
    }
    
    // Issue 3: Empty correct answer
    if (empty(trim($correctAnswer))) {
        $issues[] = "Correct answer is empty";
    }
    
    // Issue 4: Whitespace in correct answer
    if ($correctAnswer !== trim($correctAnswer)) {
        $issues[] = "Correct answer has whitespace: '" . $correctAnswer . "'";
    }
    
    // Issue 5: Correct answer not found in options
    $correctAnswerFound = false;
    $correctAnswerNorm = trim($correctAnswer);
    
    foreach ($options as $key => $value) {
        $keyNorm = trim($key);
        $valueNorm = trim($value);
        
        // Check if correct answer matches key or value
        if (strcasecmp($keyNorm, $correctAnswerNorm) === 0 || 
            strcasecmp($valueNorm, $correctAnswerNorm) === 0) {
            $correctAnswerFound = true;
            break;
        }
        
        // Check without letter prefix (e.g., "A. Text" → "Text")
        $valueClean = preg_replace('/^[A-E]\.\s*/i', '', $valueNorm);
        $correctClean = preg_replace('/^[A-E]\.\s*/i', '', $correctAnswerNorm);
        
        if (strcasecmp($valueClean, $correctClean) === 0) {
            $correctAnswerFound = true;
            break;
        }
    }
    
    if (!$correctAnswerFound) {
        $issues[] = "Correct answer '{$correctAnswer}' not found in options";
        $issues[] = "  Available options: " . implode(', ', array_keys($options));
    }
    
    // Issue 6: Inconsistent option format
    $hasLetterKeys = false;
    $hasNumericKeys = false;
    foreach ($options as $key => $value) {
        if (preg_match('/^[A-E]$/i', $key)) {
            $hasLetterKeys = true;
        } elseif (is_numeric($key)) {
            $hasNumericKeys = true;
        }
    }
    
    if ($hasLetterKeys && $hasNumericKeys) {
        $issues[] = "Mixed option key formats (both letters and numbers)";
    }
    
    return $issues;
}

// Check all question tables
$tables = [
    'chapter_questions' => 'chapter_id',
    'questions' => 'chapter_id',
    'final_exam_questions' => 'course_id'
];

$totalBroken = 0;
$totalChecked = 0;

foreach ($tables as $tableName => $foreignKey) {
    echo "=== Checking {$tableName} ===\n";
    
    try {
        $questions = DB::table($tableName)->get();
        echo "Total questions: " . count($questions) . "\n\n";
        
        foreach ($questions as $q) {
            $totalChecked++;
            $issues = checkQuestion($q, $tableName);
            
            if (!empty($issues)) {
                $totalBroken++;
                echo "🚨 BROKEN - Question ID {$q->id} ({$foreignKey}: {$q->$foreignKey})\n";
                echo "   Text: " . substr($q->question_text, 0, 60) . "...\n";
                foreach ($issues as $issue) {
                    echo "   - {$issue}\n";
                }
                echo "\n";
            }
        }
    } catch (\Exception $e) {
        echo "⚠️  Error checking {$tableName}: " . $e->getMessage() . "\n\n";
    }
}

echo "\n=== SUMMARY ===\n";
echo "Total questions checked: {$totalChecked}\n";
echo "Broken questions found: {$totalBroken}\n";

if ($totalBroken > 0) {
    echo "\n🔧 To fix these issues, run: php fix_quiz_answer_formats.php\n";
} else {
    echo "\n✅ All questions appear to be correctly formatted!\n";
}
