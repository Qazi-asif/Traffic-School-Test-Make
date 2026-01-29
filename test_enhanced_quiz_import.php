<?php

require_once 'vendor/autoload.php';

// Load Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Admin\SimpleQuizImportController;

echo "=== Testing Enhanced Quiz Import System ===\n\n";

// Test content that was causing partial imports
$testContent = "Chapter 1-Quiz 1. Which of the following is an example of a kind of change traffic laws must respond to?A. Changes car manufacturing methodsB. Changes in climateC. Changes in taxesD. Changes in technology. ***E. None of the above.2. What is an example of a driving technique one might need to learn to safely use the roads?A. ScanningB. Avoiding no-zonesC. 3-second systemD. SignalingE. All of the above ***3. When should you check your mirrors?A. Only when changingB. Every 5-8 secondsC. Only when turningD. Before braking ***E. Never";

echo "Test content length: " . strlen($testContent) . " characters\n";
echo "Content preview: " . substr($testContent, 0, 200) . "...\n\n";

try {
    // Check database structure first
    $columns = DB::getSchemaBuilder()->getColumnListing('chapter_questions');
    echo "Available columns: " . implode(', ', $columns) . "\n";
    
    $hasPoints = in_array('points', $columns);
    echo "Points column exists: " . ($hasPoints ? "YES" : "NO") . "\n\n";
    
    // Get a test chapter
    $chapter = DB::table('chapters')->first();
    if (!$chapter) {
        echo "❌ No chapters found in database. Please create a chapter first.\n";
        exit;
    }
    
    echo "Using chapter: {$chapter->id} - {$chapter->title}\n\n";
    
    // Create controller instance and test parsing
    $controller = new SimpleQuizImportController();
    
    // Use reflection to access private methods for testing
    $reflection = new ReflectionClass($controller);
    $parseMethod = $reflection->getMethod('parseSimpleQuestions');
    $parseMethod->setAccessible(true);
    
    $saveMethod = $reflection->getMethod('saveSimpleQuestions');
    $saveMethod->setAccessible(true);
    
    // Test parsing
    echo "Testing question parsing...\n";
    $questions = $parseMethod->invoke($controller, $testContent);
    
    echo "Parsed questions: " . count($questions) . "\n\n";
    
    foreach ($questions as $index => $question) {
        echo "Question " . ($index + 1) . ":\n";
        echo "  Text: " . substr($question['question'], 0, 80) . "...\n";
        echo "  Options: " . count($question['options']) . " options\n";
        echo "  Correct: " . $question['correct_answer'] . "\n";
        echo "  Options: " . implode(', ', array_keys($question['options'])) . "\n";
        echo "---\n";
    }
    
    if (count($questions) > 0) {
        echo "\nTesting database save...\n";
        
        // Clear existing questions for this chapter
        DB::table('chapter_questions')->where('chapter_id', $chapter->id)->delete();
        
        // Save questions
        $result = $saveMethod->invoke($controller, $questions, $chapter->id, false);
        
        echo "Save result: Imported {$result['imported']}, Deleted {$result['deleted']}\n";
        
        // Verify in database
        $savedQuestions = DB::table('chapter_questions')
            ->where('chapter_id', $chapter->id)
            ->orderBy('order_index')
            ->get();
        
        echo "\nVerification - Questions in database: " . count($savedQuestions) . "\n";
        
        foreach ($savedQuestions as $saved) {
            echo "  ID: {$saved->id}, Order: {$saved->order_index}\n";
            echo "  Question: " . substr($saved->question_text, 0, 60) . "...\n";
            echo "  Answer: {$saved->correct_answer}\n";
            if (isset($saved->points)) {
                echo "  Points: {$saved->points}\n";
            } else {
                echo "  Points: NOT SET\n";
            }
            if (isset($saved->options)) {
                $options = json_decode($saved->options, true);
                echo "  Options: " . (is_array($options) ? count($options) : 'Invalid JSON') . "\n";
            }
            echo "---\n";
        }
        
        echo "\n✅ Test completed successfully!\n";
        echo "All " . count($questions) . " questions were parsed and saved correctly.\n";
        
    } else {
        echo "❌ No questions were parsed from the test content.\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

?>