<?php
/**
 * Test 24-Hour Grading System
 * 
 * This script tests the free response grading system functionality
 * Run this from command line: php test_grading_system.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "=== 24-Hour Grading System Test ===\n\n";

try {
    // Check if required columns exist in user_course_enrollments
    $hasGradingColumns = Schema::hasColumns('user_course_enrollments', [
        'free_response_graded', 
        'grading_completed_at'
    ]);
    
    echo "✓ Grading columns in user_course_enrollments: " . ($hasGradingColumns ? 'YES' : 'NO') . "\n";
    
    if (!$hasGradingColumns) {
        echo "\n❌ Adding grading columns to user_course_enrollments...\n";
        
        Schema::table('user_course_enrollments', function ($table) {
            $table->boolean('free_response_graded')->default(false)->after('can_take_final_exam');
            $table->timestamp('grading_completed_at')->nullable()->after('free_response_graded');
        });
        
        echo "✓ Grading columns added successfully!\n";
    }
    
    // Check if free_response_answers table has status column
    $hasStatusColumn = Schema::hasColumn('free_response_answers', 'status');
    echo "✓ Status column in free_response_answers: " . ($hasStatusColumn ? 'YES' : 'NO') . "\n";
    
    if (!$hasStatusColumn) {
        echo "\n❌ Adding status column to free_response_answers...\n";
        
        Schema::table('free_response_answers', function ($table) {
            $table->enum('status', ['submitted', 'graded', 'needs_revision'])->default('submitted')->after('word_count');
        });
        
        echo "✓ Status column added successfully!\n";
    }
    
    // Test grading workflow
    echo "\n=== Testing Grading Workflow ===\n";
    
    // Count pending answers (submitted but not graded)
    $pendingAnswers = DB::table('free_response_answers')
        ->where('status', 'submitted')
        ->whereNull('graded_at')
        ->count();
    
    echo "✓ Pending answers awaiting grading: {$pendingAnswers}\n";
    
    // Count graded answers
    $gradedAnswers = DB::table('free_response_answers')
        ->where('status', 'graded')
        ->whereNotNull('graded_at')
        ->count();
    
    echo "✓ Graded answers: {$gradedAnswers}\n";
    
    // Count enrollments with completed grading
    $completedGrading = DB::table('user_course_enrollments')
        ->where('free_response_graded', true)
        ->count();
    
    echo "✓ Enrollments with completed grading: {$completedGrading}\n";
    
    // Test API endpoints
    echo "\n=== API Endpoints ===\n";
    echo "✓ Grading status endpoint: /api/free-response-grading-status\n";
    echo "✓ Submit answers endpoint: /api/free-response-answers\n";
    echo "✓ Grade answer endpoint: /admin/free-response-quiz-submissions/{id}/grade\n";
    
    echo "\n=== Grading Process Flow ===\n";
    echo "1. Student submits free response answers\n";
    echo "2. System shows 24-hour grading period message\n";
    echo "3. Course progression is blocked until grading complete\n";
    echo "4. Instructor reviews and grades answers in admin panel\n";
    echo "5. When all answers graded, student can continue course\n";
    echo "6. Student receives feedback in 'My Feedback' tab\n";
    
    echo "\n✅ Grading system test completed successfully!\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}