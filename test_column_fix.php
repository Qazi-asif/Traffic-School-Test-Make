<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Testing Column Fix ===\n\n";

try {
    // Test the column checking functionality
    $controller = new App\Http\Controllers\Admin\QuizImportController();
    
    // Use reflection to access private methods
    $reflection = new ReflectionClass($controller);
    $columnExistsMethod = $reflection->getMethod('columnExists');
    $columnExistsMethod->setAccessible(true);
    
    $ensureColumnsMethod = $reflection->getMethod('ensureRequiredColumns');
    $ensureColumnsMethod->setAccessible(true);
    
    echo "1. Checking if columns exist...\n";
    $questionTypeExists = $columnExistsMethod->invoke($controller, 'chapter_questions', 'question_type');
    $optionsExists = $columnExistsMethod->invoke($controller, 'chapter_questions', 'options');
    
    echo "   question_type: " . ($questionTypeExists ? "✅ Exists" : "❌ Missing") . "\n";
    echo "   options: " . ($optionsExists ? "✅ Exists" : "❌ Missing") . "\n";
    
    echo "\n2. Ensuring required columns exist...\n";
    $ensureColumnsMethod->invoke($controller);
    echo "   Column creation attempted\n";
    
    echo "\n3. Re-checking columns...\n";
    $questionTypeExists = $columnExistsMethod->invoke($controller, 'chapter_questions', 'question_type');
    $optionsExists = $columnExistsMethod->invoke($controller, 'chapter_questions', 'options');
    
    echo "   question_type: " . ($questionTypeExists ? "✅ Exists" : "❌ Missing") . "\n";
    echo "   options: " . ($optionsExists ? "✅ Exists" : "❌ Missing") . "\n";
    
    echo "\n4. Testing quiz import functionality...\n";
    
    // Test data
    $testQuestions = [
        [
            'question' => 'What is the speed limit in a school zone?',
            'options' => ['A' => '15 mph', 'B' => '25 mph', 'C' => '35 mph', 'D' => '45 mph'],
            'correct_answer' => 'B'
        ]
    ];
    
    // Test the saveQuestions method
    $saveQuestionsMethod = $reflection->getMethod('saveQuestions');
    $saveQuestionsMethod->setAccessible(true);
    
    // Assume chapter ID 1 exists, or create a test chapter
    $chapterId = 1;
    
    try {
        $result = $saveQuestionsMethod->invoke($controller, $testQuestions, $chapterId, false);
        echo "   ✅ Quiz import test successful!\n";
        echo "   Imported: " . $result['imported'] . " questions\n";
        
        // Clean up test data
        DB::table('chapter_questions')
            ->where('chapter_id', $chapterId)
            ->where('question_text', 'What is the speed limit in a school zone?')
            ->delete();
        echo "   ✅ Test data cleaned up\n";
        
    } catch (Exception $e) {
        echo "   ❌ Quiz import test failed: " . $e->getMessage() . "\n";
    }
    
    echo "\n🎉 Column fix test completed!\n";
    echo "The quiz import system should now work without column errors.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}

?>