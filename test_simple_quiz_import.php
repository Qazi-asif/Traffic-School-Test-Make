<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Testing Simple Quiz Import System ===\n\n";

try {
    // Test the simple parsing function
    $controller = new App\Http\Controllers\Admin\SimpleQuizImportController();
    $reflection = new ReflectionClass($controller);
    $parseMethod = $reflection->getMethod('parseSimpleQuestions');
    $parseMethod->setAccessible(true);

    $testContent = "1. What is the speed limit in a school zone?
A. 15 mph
B. 25 mph **
C. 35 mph
D. 45 mph

2. When should you use your turn signal?
A. Only when turning left
B. Only when turning right
C. Before any turn or lane change **
D. Only on highways";

    echo "1. Testing question parsing...\n";
    $questions = $parseMethod->invoke($controller, $testContent);
    
    echo "Questions found: " . count($questions) . "\n";
    
    foreach ($questions as $i => $q) {
        echo "Question " . ($i + 1) . ":\n";
        echo "  Text: " . $q['question'] . "\n";
        echo "  Options: " . count($q['options']) . " (" . implode(', ', array_keys($q['options'])) . ")\n";
        echo "  Correct: " . $q['correct_answer'] . "\n\n";
    }

    // Test database saving
    echo "2. Testing database save...\n";
    
    // Get or create a test chapter
    $chapter = DB::table('chapters')->first();
    if (!$chapter) {
        $courseId = DB::table('courses')->insertGetId([
            'title' => 'Simple Test Course',
            'description' => 'Test course',
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        $chapterId = DB::table('chapters')->insertGetId([
            'course_id' => $courseId,
            'title' => 'Simple Test Chapter',
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
    
    // Test the save method
    $saveMethod = $reflection->getMethod('saveSimpleQuestions');
    $saveMethod->setAccessible(true);
    
    $result = $saveMethod->invoke($controller, $questions, $chapterId, false);
    
    echo "Save result:\n";
    echo "  Imported: " . $result['imported'] . "\n";
    echo "  Deleted: " . $result['deleted'] . "\n";
    
    // Verify in database
    $savedQuestions = DB::table('chapter_questions')
        ->where('chapter_id', $chapterId)
        ->where('question_text', 'LIKE', '%speed limit%')
        ->get();
    
    echo "  Verified in DB: " . count($savedQuestions) . " questions\n";
    
    if (count($savedQuestions) > 0) {
        $firstQuestion = $savedQuestions->first();
        echo "  Sample question: " . substr($firstQuestion->question_text, 0, 50) . "...\n";
        echo "  Correct answer: " . $firstQuestion->correct_answer . "\n";
        
        if (isset($firstQuestion->options)) {
            $options = json_decode($firstQuestion->options, true);
            echo "  Options count: " . count($options) . "\n";
        }
    }
    
    // Clean up test data
    DB::table('chapter_questions')
        ->where('chapter_id', $chapterId)
        ->where('question_text', 'LIKE', '%speed limit%')
        ->delete();
    echo "  Test data cleaned up\n";
    
    echo "\n✅ Simple Quiz Import System Test PASSED!\n";
    echo "\nAccess the system at: /admin/simple-quiz-import\n";
    
} catch (Exception $e) {
    echo "❌ Test failed: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}

?>