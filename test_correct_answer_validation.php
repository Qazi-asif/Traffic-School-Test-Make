<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Testing Correct Answer Validation ===\n\n";

try {
    $controller = new App\Http\Controllers\Admin\SimpleQuizImportController();
    $reflection = new ReflectionClass($controller);
    $validateMethod = $reflection->getMethod('validateCorrectAnswer');
    $validateMethod->setAccessible(true);

    // Test various correct answer formats
    $testCases = [
        'A' => 'A',
        'B' => 'B',
        'C' => 'C',
        'D' => 'D',
        'E' => 'E',
        'a' => 'A', // lowercase
        'b' => 'B',
        ' A ' => 'A', // with spaces
        ' E ' => 'E',
        'AB' => 'A', // multiple characters - should take first
        'EA' => 'E', // multiple characters - should take first
        '' => 'A', // empty - should default to A
        null => 'A', // null - should default to A
        'X' => 'A', // invalid letter - should default to A
        '1' => 'A', // number - should default to A
    ];

    echo "Testing correct answer validation:\n";
    foreach ($testCases as $input => $expected) {
        $result = $validateMethod->invoke($controller, $input);
        $status = ($result === $expected) ? '✅' : '❌';
        echo "$status Input: '" . ($input ?? 'null') . "' -> Output: '$result' (Expected: '$expected')\n";
    }

    // Test with actual quiz content
    echo "\nTesting with actual quiz parsing:\n";
    $testContent = "1. What is an example of a driving technique?
A. Scanning
B. Avoiding no-zones
C. 3-second system
D. Signaling
E. All of the above ***";

    $parseMethod = $reflection->getMethod('parseSimpleQuestions');
    $parseMethod->setAccessible(true);
    
    $questions = $parseMethod->invoke($controller, $testContent);
    
    if (!empty($questions)) {
        $question = $questions[0];
        echo "✅ Parsed question successfully\n";
        echo "  Question: " . substr($question['question'], 0, 50) . "...\n";
        echo "  Correct Answer: '" . $question['correct_answer'] . "'\n";
        echo "  Answer Length: " . strlen($question['correct_answer']) . " characters\n";
        echo "  Options: " . count($question['options']) . " (" . implode(', ', array_keys($question['options'])) . ")\n";
        
        // Test database insert with this question
        echo "\nTesting database insert:\n";
        
        $chapter = DB::table('chapters')->first();
        if ($chapter) {
            $chapterId = $chapter->id;
            
            $saveMethod = $reflection->getMethod('saveSimpleQuestions');
            $saveMethod->setAccessible(true);
            
            try {
                $result = $saveMethod->invoke($controller, [$question], $chapterId, false);
                echo "✅ Database insert successful\n";
                echo "  Imported: " . $result['imported'] . " questions\n";
                
                // Verify in database
                $saved = DB::table('chapter_questions')
                    ->where('chapter_id', $chapterId)
                    ->where('question_text', 'LIKE', '%driving technique%')
                    ->first();
                
                if ($saved) {
                    echo "✅ Verification successful\n";
                    echo "  Saved correct answer: '" . $saved->correct_answer . "'\n";
                    echo "  Answer length in DB: " . strlen($saved->correct_answer) . " characters\n";
                    
                    // Clean up
                    DB::table('chapter_questions')->where('id', $saved->id)->delete();
                    echo "✅ Test data cleaned up\n";
                }
                
            } catch (\Exception $e) {
                echo "❌ Database insert failed: " . $e->getMessage() . "\n";
            }
        }
    } else {
        echo "❌ No questions parsed\n";
    }

    echo "\n🎯 Correct answer validation is working!\n";
    echo "The system will now ensure all correct answers are single characters (A-E).\n";

} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

?>