<?php
// Check free response tables structure
require_once 'vendor/autoload.php';

// Load Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== CHECKING FREE RESPONSE TABLES ===\n\n";

// Check free_response_questions table structure
echo "=== free_response_questions table structure ===\n";
try {
    $columns = \DB::select("DESCRIBE free_response_questions");
    foreach ($columns as $column) {
        echo "- {$column->Field} ({$column->Type}) " . ($column->Null === 'YES' ? 'NULL' : 'NOT NULL') . "\n";
    }
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

echo "\n=== free_response_quiz_placements table structure ===\n";
try {
    $columns = \DB::select("DESCRIBE free_response_quiz_placements");
    foreach ($columns as $column) {
        echo "- {$column->Field} ({$column->Type}) " . ($column->Null === 'YES' ? 'NULL' : 'NOT NULL') . "\n";
    }
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

echo "\n=== Testing placement_id constraint ===\n";
// Check if there are any placements
$placements = \DB::table('free_response_quiz_placements')->get();
echo "Available placements: " . $placements->count() . "\n";
foreach ($placements as $placement) {
    echo "- ID: {$placement->id}, Course: {$placement->course_id}, Title: {$placement->quiz_title}\n";
}

echo "\n=== Testing question creation ===\n";
// Try to create a test question
try {
    $testData = [
        'course_id' => 17, // Your course ID
        'placement_id' => $placements->first()->id ?? 1, // Use first placement
        'question_text' => 'Test question - can be deleted',
        'order_index' => 999,
        'points' => 5,
        'is_active' => true,
    ];
    
    echo "Attempting to create question with data:\n";
    print_r($testData);
    
    $question = \App\Models\FreeResponseQuestion::create($testData);
    echo "✅ SUCCESS: Question created with ID: {$question->id}\n";
    
    // Clean up - delete the test question
    $question->delete();
    echo "✅ Test question deleted\n";
    
} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
?>