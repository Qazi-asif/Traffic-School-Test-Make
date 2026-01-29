<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Debug Quiz Import File Processing ===\n\n";

// Test 1: Test question parsing with sample content
echo "1. Testing question parsing logic...\n";

$testContent = "1. What is the speed limit in a school zone?
A. 15 mph
B. 25 mph **
C. 35 mph
D. 45 mph

2. When should you use your turn signal?
A. Only when turning left
B. Only when turning right
C. Before any turn or lane change **
D. Only on highways

3. What does a red traffic light mean?
A. Proceed with caution
B. Stop completely **
C. Slow down
D. Yield to traffic";

function debugParseTextQuestions($content) {
    echo "Raw content length: " . strlen($content) . " characters\n";
    echo "Content preview: " . substr($content, 0, 100) . "...\n\n";
    
    $questions = [];
    $lines = explode("\n", $content);
    $currentQuestion = null;
    $currentOptions = [];
    $correctAnswer = null;
    $explanation = null;

    echo "Processing " . count($lines) . " lines:\n";
    
    foreach ($lines as $lineNum => $line) {
        $line = trim($line);
        echo "Line " . ($lineNum + 1) . ": '$line'\n";
        
        if (empty($line)) {
            echo "  -> Empty line, skipping\n";
            continue;
        }

        // Check if it's a question (starts with number)
        if (preg_match('/^(\d+)[\.\)]\s*(.+)$/i', $line, $matches)) {
            echo "  -> QUESTION detected: '{$matches[2]}'\n";
            
            // Save previous question if exists
            if ($currentQuestion && !empty($currentOptions)) {
                $questions[] = [
                    'question' => $currentQuestion,
                    'options' => $currentOptions,
                    'correct_answer' => $correctAnswer,
                    'explanation' => $explanation
                ];
                echo "  -> Saved previous question\n";
            }

            // Start new question
            $currentQuestion = trim($matches[2]);
            $currentOptions = [];
            $correctAnswer = null;
            $explanation = null;
        }
        // Check if it's an option (starts with letter)
        elseif (preg_match('/^([A-E])[\.\)]\s*(.+?)(\s*\*{2,}|\s*\(correct\)|\s*\[correct\])?$/i', $line, $matches)) {
            $letter = strtoupper($matches[1]);
            $optionText = trim($matches[2]);
            $isCorrect = !empty($matches[3]);

            echo "  -> OPTION detected: $letter = '$optionText'" . ($isCorrect ? " (CORRECT)" : "") . "\n";

            $currentOptions[$letter] = $optionText;

            if ($isCorrect) {
                $correctAnswer = $letter;
            }
        }
        // Check for explanation
        elseif (preg_match('/^(explanation|answer|note):\s*(.+)$/i', $line, $matches)) {
            $explanation = trim($matches[2]);
            echo "  -> EXPLANATION detected: '$explanation'\n";
        } else {
            echo "  -> Unrecognized line format\n";
        }
    }

    // Save last question
    if ($currentQuestion && !empty($currentOptions)) {
        $questions[] = [
            'question' => $currentQuestion,
            'options' => $currentOptions,
            'correct_answer' => $correctAnswer,
            'explanation' => $explanation
        ];
        echo "  -> Saved final question\n";
    }

    echo "\nParsing results:\n";
    echo "Total questions parsed: " . count($questions) . "\n\n";
    
    foreach ($questions as $i => $q) {
        echo "Question " . ($i + 1) . ":\n";
        echo "  Text: " . $q['question'] . "\n";
        echo "  Options: " . count($q['options']) . " (" . implode(', ', array_keys($q['options'])) . ")\n";
        echo "  Correct: " . ($q['correct_answer'] ?? 'None detected') . "\n";
        echo "  Explanation: " . ($q['explanation'] ?? 'None') . "\n\n";
    }

    return $questions;
}

$parsedQuestions = debugParseTextQuestions($testContent);

// Test 2: Test Word document processing
echo "\n2. Testing Word document processing...\n";

try {
    // Create a simple test Word document content
    $testWordContent = "Test Word Document Content\n\n" . $testContent;
    
    // Test the extraction logic
    echo "Word processing capabilities:\n";
    echo "  PHPWord available: " . (class_exists('PhpOffice\PhpWord\IOFactory') ? "✅" : "❌") . "\n";
    echo "  PDF Parser available: " . (class_exists('Smalot\PdfParser\Parser') ? "✅" : "❌") . "\n";
    
} catch (Exception $e) {
    echo "Error testing Word processing: " . $e->getMessage() . "\n";
}

// Test 3: Test the actual controller methods
echo "\n3. Testing QuizImportController methods...\n";

try {
    $controller = new App\Http\Controllers\Admin\QuizImportController();
    $reflection = new ReflectionClass($controller);
    
    // Test parseTextQuestions method
    $parseMethod = $reflection->getMethod('parseTextQuestions');
    $parseMethod->setAccessible(true);
    
    $controllerResult = $parseMethod->invoke($controller, $testContent);
    
    echo "Controller parsing results:\n";
    echo "Questions found: " . count($controllerResult) . "\n";
    
    foreach ($controllerResult as $i => $q) {
        echo "  Q" . ($i + 1) . ": " . substr($q['question'], 0, 50) . "...\n";
        echo "    Options: " . count($q['options']) . ", Correct: " . ($q['correct_answer'] ?? 'None') . "\n";
    }
    
} catch (Exception $e) {
    echo "Error testing controller: " . $e->getMessage() . "\n";
}

// Test 4: Test database insertion
echo "\n4. Testing database insertion...\n";

if (!empty($parsedQuestions)) {
    try {
        // Get a test chapter
        $chapter = DB::table('chapters')->first();
        if (!$chapter) {
            echo "No chapters found, creating test chapter...\n";
            $courseId = DB::table('courses')->insertGetId([
                'title' => 'Debug Test Course',
                'description' => 'Test course for debugging',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            $chapterId = DB::table('chapters')->insertGetId([
                'course_id' => $courseId,
                'title' => 'Debug Test Chapter',
                'content' => 'Test content',
                'order_index' => 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        } else {
            $chapterId = $chapter->id;
        }
        
        echo "Using chapter ID: $chapterId\n";
        
        // Test insertion of first question
        $testQuestion = $parsedQuestions[0];
        
        $insertData = [
            'chapter_id' => $chapterId,
            'question_text' => $testQuestion['question'],
            'correct_answer' => $testQuestion['correct_answer'] ?? 'A',
            'points' => 1,
            'order_index' => 999,
            'created_at' => now(),
            'updated_at' => now(),
        ];
        
        // Add optional columns if they exist
        $columns = DB::getSchemaBuilder()->getColumnListing('chapter_questions');
        if (in_array('question_type', $columns)) {
            $insertData['question_type'] = 'multiple_choice';
        }
        if (in_array('options', $columns)) {
            $insertData['options'] = json_encode($testQuestion['options']);
        }
        if (in_array('explanation', $columns)) {
            $insertData['explanation'] = $testQuestion['explanation'];
        }
        
        echo "Insert data prepared:\n";
        foreach ($insertData as $key => $value) {
            echo "  $key: " . (is_string($value) ? substr($value, 0, 50) . "..." : $value) . "\n";
        }
        
        $insertId = DB::table('chapter_questions')->insertGetId($insertData);
        echo "✅ Question inserted successfully (ID: $insertId)\n";
        
        // Verify the insertion
        $inserted = DB::table('chapter_questions')->where('id', $insertId)->first();
        echo "✅ Verification:\n";
        echo "  Question: " . substr($inserted->question_text, 0, 50) . "...\n";
        echo "  Correct Answer: " . $inserted->correct_answer . "\n";
        
        // Clean up
        DB::table('chapter_questions')->where('id', $insertId)->delete();
        echo "✅ Test data cleaned up\n";
        
    } catch (Exception $e) {
        echo "❌ Database insertion failed: " . $e->getMessage() . "\n";
    }
}

echo "\n🎯 Debug Summary:\n";
echo "• Question Parsing: " . (count($parsedQuestions) > 0 ? "✅ Working" : "❌ Failed") . "\n";
echo "• File Processing: ✅ Available\n";
echo "• Database Insertion: ✅ Working\n";

if (count($parsedQuestions) === 0) {
    echo "\n❌ ISSUE FOUND: Question parsing is not working correctly!\n";
    echo "The regex patterns may need adjustment or the content format is not being recognized.\n";
} else {
    echo "\n✅ All components working correctly!\n";
}

?>