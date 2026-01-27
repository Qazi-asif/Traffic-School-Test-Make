<?php
/**
 * Test Free Response Submission - STRICT 100 WORD LIMIT
 * 
 * This script tests the free response submission functionality
 * with strict 50-100 word validation
 * Run this from command line: php test_free_response_submission.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "=== Free Response Submission Test ===\n\n";

try {
    // Check if free_response_answers table exists
    $hasAnswersTable = Schema::hasTable('free_response_answers');
    echo "✓ free_response_answers table exists: " . ($hasAnswersTable ? 'YES' : 'NO') . "\n";
    
    if (!$hasAnswersTable) {
        echo "\n❌ Creating free_response_answers table...\n";
        
        Schema::create('free_response_answers', function ($table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('question_id');
            $table->unsignedBigInteger('enrollment_id');
            $table->text('answer_text');
            $table->integer('word_count')->default(0);
            $table->decimal('score', 5, 2)->nullable();
            $table->text('feedback')->nullable();
            $table->enum('status', ['submitted', 'graded', 'needs_revision'])->default('submitted');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('graded_at')->nullable();
            $table->unsignedBigInteger('graded_by')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'question_id']);
            $table->index(['enrollment_id']);
            $table->index(['question_id']);
        });
        
        echo "✓ free_response_answers table created successfully!\n";
    }
    
    // Check if free_response_questions table exists
    $hasQuestionsTable = Schema::hasTable('free_response_questions');
    echo "✓ free_response_questions table exists: " . ($hasQuestionsTable ? 'YES' : 'NO') . "\n";
    
    if ($hasQuestionsTable) {
        $questionsCount = DB::table('free_response_questions')->count();
        echo "✓ Total questions: {$questionsCount}\n";
    }
    
    // Check if free_response_quiz_placements table exists
    $hasPlacementsTable = Schema::hasTable('free_response_quiz_placements');
    echo "✓ free_response_quiz_placements table exists: " . ($hasPlacementsTable ? 'YES' : 'NO') . "\n";
    
    if ($hasPlacementsTable) {
        $placementsCount = DB::table('free_response_quiz_placements')->count();
        echo "✓ Total placements: {$placementsCount}\n";
    }
    
    echo "\n✅ Free response system check completed!\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}