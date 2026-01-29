<?php

require_once 'vendor/autoload.php';

// Load Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Chapter Questions Table Analysis ===\n\n";

try {
    // Get table columns
    $columns = DB::getSchemaBuilder()->getColumnListing('chapter_questions');
    echo "Available columns: " . implode(', ', $columns) . "\n\n";
    
    // Check if points column exists
    $hasPoints = in_array('points', $columns);
    echo "Points column exists: " . ($hasPoints ? "YES" : "NO") . "\n";
    
    // Check if question_type column exists
    $hasQuestionType = in_array('question_type', $columns);
    echo "Question_type column exists: " . ($hasQuestionType ? "YES" : "NO") . "\n";
    
    // Check if options column exists
    $hasOptions = in_array('options', $columns);
    echo "Options column exists: " . ($hasOptions ? "YES" : "NO") . "\n\n";
    
    // Count records
    $count = DB::table('chapter_questions')->count();
    echo "Total questions in database: {$count}\n\n";
    
    if ($count > 0) {
        echo "Sample records:\n";
        $samples = DB::table('chapter_questions')->limit(3)->get();
        foreach ($samples as $sample) {
            echo "ID: {$sample->id}, Chapter: {$sample->chapter_id}\n";
            echo "Question: " . substr($sample->question_text, 0, 80) . "...\n";
            echo "Correct Answer: {$sample->correct_answer}\n";
            if (isset($sample->points)) {
                echo "Points: {$sample->points}\n";
            } else {
                echo "Points: NOT SET (column missing)\n";
            }
            if (isset($sample->options)) {
                echo "Options: " . substr($sample->options, 0, 100) . "...\n";
            } else {
                echo "Options: NOT SET (column missing)\n";
            }
            echo "---\n";
        }
    }
    
    // Check for any recent imports
    echo "\nRecent imports (last 10):\n";
    $recent = DB::table('chapter_questions')
        ->orderBy('created_at', 'desc')
        ->limit(10)
        ->get(['id', 'chapter_id', 'question_text', 'correct_answer', 'created_at']);
    
    foreach ($recent as $q) {
        echo "ID: {$q->id}, Chapter: {$q->chapter_id}, Created: {$q->created_at}\n";
        echo "Question: " . substr($q->question_text, 0, 60) . "...\n";
        echo "Answer: {$q->correct_answer}\n---\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

?>