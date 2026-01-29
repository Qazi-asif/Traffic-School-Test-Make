<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Testing Actual Quiz Format ===\n\n";

// Use the actual content from your file
$actualContent = "Chapter 1-Quiz 1. Which of the following is an example of a kind of change traffic laws must respond to?A. Changes car manufacturing methodsB. Changes in climateC. Changes in taxesD. Changes in technology. ***E. None of the above.2. What is an example of a driving technique on";

// Let's also test with a more complete version
$testContent = "Chapter 1-Quiz 
1. Which of the following is an example of a kind of change traffic laws must respond to?
A. Changes car manufacturing methods
B. Changes in climate
C. Changes in taxes
D. Changes in technology. ***
E. None of the above.

2. What is an example of a driving technique?
A. Defensive driving
B. Aggressive driving ***
C. Distracted driving
D. Reckless driving
E. None of the above";

try {
    $controller = new App\Http\Controllers\Admin\SimpleQuizImportController();
    $reflection = new ReflectionClass($controller);
    $parseMethod = $reflection->getMethod('parseSimpleQuestions');
    $parseMethod->setAccessible(true);

    echo "1. Testing with actual extracted content:\n";
    echo "Content: " . substr($actualContent, 0, 200) . "...\n\n";
    
    $questions1 = $parseMethod->invoke($controller, $actualContent);
    echo "Questions found: " . count($questions1) . "\n";
    
    foreach ($questions1 as $i => $q) {
        echo "Question " . ($i + 1) . ":\n";
        echo "  Text: " . $q['question'] . "\n";
        echo "  Options: " . count($q['options']) . " (" . implode(', ', array_keys($q['options'])) . ")\n";
        echo "  Correct: " . $q['correct_answer'] . "\n";
        foreach ($q['options'] as $key => $value) {
            echo "    $key: $value\n";
        }
        echo "\n";
    }

    echo "\n2. Testing with formatted content:\n";
    $questions2 = $parseMethod->invoke($controller, $testContent);
    echo "Questions found: " . count($questions2) . "\n";
    
    foreach ($questions2 as $i => $q) {
        echo "Question " . ($i + 1) . ":\n";
        echo "  Text: " . $q['question'] . "\n";
        echo "  Options: " . count($q['options']) . " (" . implode(', ', array_keys($q['options'])) . ")\n";
        echo "  Correct: " . $q['correct_answer'] . "\n";
        foreach ($q['options'] as $key => $value) {
            echo "    $key: $value\n";
        }
        echo "\n";
    }

    // Test line-by-line parsing to debug
    echo "\n3. Debug line-by-line parsing:\n";
    $lines = explode("\n", $testContent);
    foreach ($lines as $lineNum => $line) {
        $line = trim($line);
        if (empty($line)) continue;
        
        echo "Line " . ($lineNum + 1) . ": '$line'\n";
        
        // Test question pattern
        if (preg_match('/^(\d+)[\.\)]\s*(.+)/', $line, $matches)) {
            echo "  -> QUESTION: '{$matches[2]}'\n";
        }
        // Test option pattern
        elseif (preg_match('/^([A-E])[\.\)]\s*(.+?)(\s*\*{2,}|\s*\(correct\)|\s*\[correct\])?$/i', $line, $matches)) {
            $letter = strtoupper($matches[1]);
            $text = trim($matches[2]);
            $isCorrect = !empty($matches[3]);
            echo "  -> OPTION: $letter = '$text'" . ($isCorrect ? " (CORRECT)" : "") . "\n";
        }
        // Test alternative correct answer pattern
        elseif (preg_match('/^([A-E])[\.\)]\s*(.+?)\s+(\*{2,})$/i', $line, $matches)) {
            $letter = strtoupper($matches[1]);
            $text = trim($matches[2]);
            echo "  -> OPTION (ALT): $letter = '$text' (CORRECT)\n";
        }
        else {
            echo "  -> UNRECOGNIZED\n";
        }
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

?>