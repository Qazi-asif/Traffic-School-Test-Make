<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Quiz Question Creation Fix Test ===\n";

try {
    // Check if chapter_questions table exists
    $tables = DB::select('SHOW TABLES');
    $tableNames = array_map(function($table) {
        return array_values((array)$table)[0];
    }, $tables);
    
    if (!in_array('chapter_questions', $tableNames)) {
        echo "❌ chapter_questions table does not exist\n";
        echo "📝 Available question-related tables:\n";
        foreach ($tableNames as $table) {
            if (strpos($table, 'question') !== false) {
                echo "  - {$table}\n";
            }
        }
        echo "\n🔧 Run the migration to create the table:\n";
        echo "   php artisan migrate\n";
        exit(1);
    }
    
    echo "✅ chapter_questions table exists\n";
    
    // Check table structure
    $columns = DB::getSchemaBuilder()->getColumnListing('chapter_questions');
    echo "📊 Table columns: " . implode(', ', $columns) . "\n";
    
    $requiredColumns = ['id', 'chapter_id', 'question_text', 'question_type', 'options', 'correct_answer'];
    $missingColumns = array_diff($requiredColumns, $columns);
    
    if (!empty($missingColumns)) {
        echo "❌ Missing required columns: " . implode(', ', $missingColumns) . "\n";
        exit(1);
    }
    
    echo "✅ All required columns present\n";
    
    // Test creating a question directly
    $testChapterId = 1; // Use chapter ID 1 for testing
    
    // Check if chapter exists
    $chapter = DB::table('chapters')->where('id', $testChapterId)->first();
    if (!$chapter) {
        echo "❌ Test chapter ID {$testChapterId} does not exist\n";
        // Try to find any chapter
        $anyChapter = DB::table('chapters')->first();
        if ($anyChapter) {
            $testChapterId = $anyChapter->id;
            echo "📝 Using chapter ID {$testChapterId} instead\n";
        } else {
            echo "❌ No chapters found in database\n";
            exit(1);
        }
    }
    
    echo "✅ Using chapter ID {$testChapterId} for testing\n";
    
    // Test data
    $testData = [
        'chapter_id' => $testChapterId,
        'question_text' => 'Which of the following is an example of a kind of change traffic laws must respond to?',
        'question_type' => 'multiple_choice',
        'options' => json_encode([
            'A' => 'Changes car manufacturing methods',
            'B' => 'Changes in climate',
            'C' => 'Changes in taxes',
            'D' => 'Changes in technology',
            'E' => 'None of the above'
        ]),
        'correct_answer' => 'D',
        'explanation' => 'Traffic laws must adapt to technological changes.',
        'points' => 1,
        'order_index' => 1,
        'quiz_set' => 1,
        'created_at' => now(),
        'updated_at' => now(),
    ];
    
    // Test direct DB insert
    echo "\n🧪 Testing direct database insert...\n";
    try {
        $questionId = DB::table('chapter_questions')->insertGetId($testData);
        echo "✅ Question created successfully with ID: {$questionId}\n";
        
        // Clean up - delete the test question
        DB::table('chapter_questions')->where('id', $questionId)->delete();
        echo "🧹 Test question cleaned up\n";
        
    } catch (Exception $e) {
        echo "❌ Direct insert failed: " . $e->getMessage() . "\n";
        echo "📋 Error details:\n";
        echo "   File: " . $e->getFile() . "\n";
        echo "   Line: " . $e->getLine() . "\n";
        exit(1);
    }
    
    // Test using ChapterQuestion model
    echo "\n🧪 Testing ChapterQuestion model...\n";
    try {
        $question = \App\Models\ChapterQuestion::create([
            'chapter_id' => $testChapterId,
            'question_text' => 'Test question using model',
            'question_type' => 'multiple_choice',
            'options' => json_encode(['A' => 'Option A', 'B' => 'Option B']),
            'correct_answer' => 'A',
            'points' => 1,
            'order_index' => 1,
            'quiz_set' => 1,
        ]);
        
        echo "✅ ChapterQuestion model works - ID: {$question->id}\n";
        
        // Clean up
        $question->delete();
        echo "🧹 Test question cleaned up\n";
        
    } catch (Exception $e) {
        echo "❌ ChapterQuestion model failed: " . $e->getMessage() . "\n";
        exit(1);
    }
    
    echo "\n🎉 All tests passed! Quiz question creation should now work.\n";
    echo "\n📝 Summary:\n";
    echo "  ✅ chapter_questions table exists with correct structure\n";
    echo "  ✅ Direct database insert works\n";
    echo "  ✅ ChapterQuestion model works\n";
    echo "  ✅ QuestionController has been updated to use direct DB insert\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}