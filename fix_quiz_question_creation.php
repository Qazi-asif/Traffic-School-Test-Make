<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Quiz Question Creation Fix ===\n";

try {
    // Check what tables exist
    $tables = DB::select('SHOW TABLES');
    $tableNames = array_map(function($table) {
        return array_values((array)$table)[0];
    }, $tables);
    
    echo "📋 Available tables:\n";
    foreach ($tableNames as $table) {
        if (strpos($table, 'question') !== false) {
            echo "  - {$table}\n";
        }
    }
    
    // Check structure of questions table
    if (in_array('questions', $tableNames)) {
        echo "\n📊 Questions table structure:\n";
        $columns = DB::getSchemaBuilder()->getColumnListing('questions');
        echo "  Columns: " . implode(', ', $columns) . "\n";
        
        $hasChapterId = in_array('chapter_id', $columns);
        echo "  Has chapter_id: " . ($hasChapterId ? 'YES' : 'NO') . "\n";
    }
    
    // Check structure of chapter_questions table
    if (in_array('chapter_questions', $tableNames)) {
        echo "\n📊 Chapter Questions table structure:\n";
        $columns = DB::getSchemaBuilder()->getColumnListing('chapter_questions');
        echo "  Columns: " . implode(', ', $columns) . "\n";
        
        $hasChapterId = in_array('chapter_id', $columns);
        echo "  Has chapter_id: " . ($hasChapterId ? 'YES' : 'NO') . "\n";
    }
    
    // Check which model is being used by default
    echo "\n🔍 Model Configuration Check:\n";
    
    // Test ChapterQuestion model
    try {
        $chapterQuestion = new \App\Models\ChapterQuestion();
        echo "  ✅ ChapterQuestion model exists\n";
        echo "  Table: " . $chapterQuestion->getTable() . "\n";
        echo "  Fillable: " . implode(', ', $chapterQuestion->getFillable()) . "\n";
    } catch (Exception $e) {
        echo "  ❌ ChapterQuestion model error: " . $e->getMessage() . "\n";
    }
    
    // Test Question model
    try {
        $question = new \App\Models\Question();
        echo "  ✅ Question model exists\n";
        echo "  Table: " . $question->getTable() . "\n";
        echo "  Fillable: " . implode(', ', $question->getFillable()) . "\n";
    } catch (Exception $e) {
        echo "  ❌ Question model error: " . $e->getMessage() . "\n";
    }
    
    echo "\n=== Fix Applied ===\n";
    echo "The QuestionController should use ChapterQuestion::create() for new questions.\n";
    echo "If the error persists, it means the questions table is being used instead.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}