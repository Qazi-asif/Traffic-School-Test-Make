<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Testing Continuous Content Parsing ===\n\n";

// Your actual content (continuous line)
$actualContent = "Chapter 1-Quiz 1. Which of the following is an example of a kind of change traffic laws must respond to?A. Changes car manufacturing methodsB. Changes in climateC. Changes in taxesD. Changes in technology. ***E. None of the above.2. What is an example of a driving technique on";

try {
    $controller = new App\Http\Controllers\Admin\SimpleQuizImportController();
    $reflection = new ReflectionClass($controller);
    
    // Test the preprocessing method
    $preprocessMethod = $reflection->getMethod('preprocessContent');
    $preprocessMethod->setAccessible(true);
    
    echo "1. Original content:\n";
    echo substr($actualContent, 0, 200) . "...\n\n";
    
    echo "2. After preprocessing:\n";
    $preprocessed = $preprocessMethod->invoke($controller, $actualContent);
    echo $preprocessed . "\n\n";
    
    // Test the parsing method
    $parseMethod = $reflection->getMethod('parseSimpleQuestions');
    $parseMethod->setAccessible(true);
    
    echo "3. Parsing results:\n";
    $questions = $parseMethod->invoke($controller, $actualContent);
    
    echo "Questions found: " . count($questions) . "\n\n";
    
    foreach ($questions as $i => $q) {
        echo "Question " . ($i + 1) . ":\n";
        echo "  Text: " . $q['question'] . "\n";
        echo "  Options: " . count($q['options']) . " (" . implode(', ', array_keys($q['options'])) . ")\n";
        echo "  Correct: " . $q['correct_answer'] . "\n";
        foreach ($q['options'] as $key => $value) {
            echo "    $key: $value\n";
        }
        echo "\n";
    }
    
    if (count($questions) > 0) {
        echo "✅ SUCCESS: Questions parsed correctly!\n";
        
        // Test database save
        echo "\n4. Testing database save...\n";
        
        $chapter = DB::table('chapters')->first();
        if (!$chapter) {
            $courseId = DB::table('courses')->insertGetId([
                'title' => 'Test Course',
                'description' => 'Test',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            $chapterId = DB::table('chapters')->insertGetId([
                'course_id' => $courseId,
                'title' => 'Test Chapter',
                'content' => 'Test',
                'order_index' => 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        } else {
            $chapterId = $chapter->id;
        }
        
        $saveMethod = $reflection->getMethod('saveSimpleQuestions');
        $saveMethod->setAccessible(true);
        
        $result = $saveMethod->invoke($controller, $questions, $chapterId, false);
        
        echo "Save result: Imported {$result['imported']} questions\n";
        
        // Verify in database
        $saved = DB::table('chapter_questions')
            ->where('chapter_id', $chapterId)
            ->where('question_text', 'LIKE', '%traffic laws%')
            ->get();
        
        echo "Verified in DB: " . count($saved) . " questions\n";
        
        if (count($saved) > 0) {
            $first = $saved->first();
            echo "Sample: " . substr($first->question_text, 0, 50) . "...\n";
            echo "Correct: " . $first->correct_answer . "\n";
        }
        
        // Clean up
        DB::table('chapter_questions')
            ->where('chapter_id', $chapterId)
            ->where('question_text', 'LIKE', '%traffic laws%')
            ->delete();
        
        echo "✅ Test completed successfully!\n";
    } else {
        echo "❌ FAILED: No questions found\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}

?>