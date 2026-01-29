<?php

// Bootstrap Laravel
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Laravel Database Column Fix ===\n";

try {
    // Use Laravel's DB facade
    $db = $app['db'];
    
    echo "Connected to database: " . $db->getDatabaseName() . "\n";
    
    // Check if table exists
    $tableExists = $db->getSchemaBuilder()->hasTable('chapter_questions');
    echo "Table exists: " . ($tableExists ? 'Yes' : 'No') . "\n";
    
    if ($tableExists) {
        // Check current columns
        $columns = $db->getSchemaBuilder()->getColumnListing('chapter_questions');
        echo "Current columns: " . implode(', ', $columns) . "\n";
        
        // Add missing columns using Schema Builder
        $db->getSchemaBuilder()->table('chapter_questions', function ($table) use ($columns) {
            if (!in_array('question_type', $columns)) {
                $table->string('question_type', 50)->default('multiple_choice')->after('question_text');
                echo "✅ Added question_type column\n";
            } else {
                echo "✅ question_type column already exists\n";
            }
            
            if (!in_array('options', $columns)) {
                $table->json('options')->nullable()->after('question_type');
                echo "✅ Added options column\n";
            } else {
                echo "✅ options column already exists\n";
            }
        });
        
        // Verify final structure
        $finalColumns = $db->getSchemaBuilder()->getColumnListing('chapter_questions');
        echo "Final columns: " . implode(', ', $finalColumns) . "\n";
        
        // Test insert
        echo "\nTesting insert...\n";
        $testId = $db->table('chapter_questions')->insertGetId([
            'chapter_id' => 1,
            'question_text' => 'Test question for column verification',
            'question_type' => 'multiple_choice',
            'options' => json_encode(['A' => 'Option A', 'B' => 'Option B', 'C' => 'Option C', 'D' => 'Option D']),
            'correct_answer' => 'A',
            'points' => 1,
            'order_index' => 999,
            'quiz_set' => 1,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        echo "✅ Test insert successful (ID: {$testId})\n";
        
        // Clean up
        $db->table('chapter_questions')->where('id', $testId)->delete();
        echo "✅ Test record cleaned up\n";
        
        echo "\n🎉 Database fix completed successfully!\n";
        echo "Quiz import system should now work without column errors.\n";
        
    } else {
        echo "❌ chapter_questions table does not exist!\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}

?>