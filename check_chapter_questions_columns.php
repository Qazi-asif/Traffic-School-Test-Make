<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Checking chapter_questions Table Columns ===\n\n";

try {
    // Get available columns
    $columns = DB::getSchemaBuilder()->getColumnListing('chapter_questions');
    
    echo "Available columns in chapter_questions table:\n";
    foreach ($columns as $column) {
        echo "  - $column\n";
    }
    
    echo "\nColumn check for quiz import:\n";
    $requiredColumns = ['chapter_id', 'question_text', 'correct_answer', 'order_index', 'created_at', 'updated_at'];
    $optionalColumns = ['question_type', 'options', 'explanation', 'points', 'quiz_set', 'is_active'];
    
    echo "\nRequired columns:\n";
    foreach ($requiredColumns as $col) {
        $exists = in_array($col, $columns);
        echo ($exists ? "✅" : "❌") . " $col\n";
    }
    
    echo "\nOptional columns:\n";
    foreach ($optionalColumns as $col) {
        $exists = in_array($col, $columns);
        echo ($exists ? "✅" : "❌") . " $col\n";
    }
    
    // Test a minimal insert
    echo "\nTesting minimal insert...\n";
    
    $testData = [
        'chapter_id' => 1,
        'question_text' => 'Test question for column verification',
        'correct_answer' => 'A',
        'order_index' => 999,
        'created_at' => now(),
        'updated_at' => now(),
    ];
    
    // Add optional columns if they exist
    if (in_array('question_type', $columns)) {
        $testData['question_type'] = 'multiple_choice';
    }
    
    if (in_array('options', $columns)) {
        $testData['options'] = json_encode(['A' => 'Test option A', 'B' => 'Test option B']);
    }
    
    try {
        $insertId = DB::table('chapter_questions')->insertGetId($testData);
        echo "✅ Test insert successful (ID: $insertId)\n";
        
        // Verify the insert
        $inserted = DB::table('chapter_questions')->where('id', $insertId)->first();
        echo "✅ Verification successful\n";
        echo "  Question: " . substr($inserted->question_text, 0, 30) . "...\n";
        echo "  Correct Answer: " . $inserted->correct_answer . "\n";
        
        // Clean up
        DB::table('chapter_questions')->where('id', $insertId)->delete();
        echo "✅ Test data cleaned up\n";
        
    } catch (\Exception $e) {
        echo "❌ Test insert failed: " . $e->getMessage() . "\n";
    }
    
    echo "\n🎯 Quiz import should now work with your table structure!\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

?>