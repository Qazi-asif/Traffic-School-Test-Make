<?php

/**
 * Test script to verify final exam completion fixes
 * Run: php test-final-exam-fixes.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\UserCourseEnrollment;
use App\Models\FinalExamResult;
use Illuminate\Support\Facades\DB;

echo "=== Final Exam Completion Fix Verification ===\n\n";

// Test 1: Check if fillable fields are set
echo "Test 1: Checking UserCourseEnrollment fillable fields...\n";
$enrollment = new UserCourseEnrollment();
$fillable = $enrollment->getFillable();

if (in_array('final_exam_completed', $fillable) && in_array('final_exam_result_id', $fillable)) {
    echo "✅ PASS: final_exam_completed and final_exam_result_id are fillable\n";
} else {
    echo "❌ FAIL: Missing fillable fields\n";
    echo "   - final_exam_completed: " . (in_array('final_exam_completed', $fillable) ? 'Yes' : 'No') . "\n";
    echo "   - final_exam_result_id: " . (in_array('final_exam_result_id', $fillable) ? 'Yes' : 'No') . "\n";
}

echo "\n";

// Test 2: Check database schema
echo "Test 2: Checking database schema...\n";
try {
    $hasColumns = DB::select("SHOW COLUMNS FROM user_course_enrollments WHERE Field IN ('final_exam_completed', 'final_exam_result_id')");
    
    if (count($hasColumns) === 2) {
        echo "✅ PASS: Database columns exist\n";
        foreach ($hasColumns as $col) {
            echo "   - {$col->Field}: {$col->Type}\n";
        }
    } else {
        echo "❌ FAIL: Missing database columns\n";
        echo "   Found: " . count($hasColumns) . " columns (expected 2)\n";
    }
} catch (Exception $e) {
    echo "❌ FAIL: Database error: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 3: Check for enrollments with passed final exams
echo "Test 3: Checking enrollments with passed final exams...\n";
try {
    $passedExams = DB::table('final_exam_results')
        ->join('user_course_enrollments', 'final_exam_results.enrollment_id', '=', 'user_course_enrollments.id')
        ->where('final_exam_results.is_passing', true)
        ->select(
            'user_course_enrollments.id',
            'user_course_enrollments.status',
            'user_course_enrollments.progress_percentage',
            'user_course_enrollments.final_exam_completed',
            'final_exam_results.final_exam_score',
            'final_exam_results.is_passing'
        )
        ->limit(5)
        ->get();
    
    if ($passedExams->count() > 0) {
        echo "Found {$passedExams->count()} enrollments with passed final exams:\n";
        foreach ($passedExams as $exam) {
            $statusIcon = $exam->status === 'completed' ? '✅' : '⚠️';
            $progressIcon = $exam->progress_percentage == 100 ? '✅' : '⚠️';
            
            echo "\n   Enrollment ID: {$exam->id}\n";
            echo "   {$statusIcon} Status: {$exam->status}\n";
            echo "   {$progressIcon} Progress: {$exam->progress_percentage}%\n";
            echo "   Final Exam Score: {$exam->final_exam_score}%\n";
            echo "   Final Exam Completed Flag: " . ($exam->final_exam_completed ? 'Yes' : 'No') . "\n";
            
            if ($exam->status !== 'completed' || $exam->progress_percentage != 100) {
                echo "   ⚠️  WARNING: This enrollment should be marked as completed with 100% progress\n";
            }
        }
    } else {
        echo "ℹ️  No enrollments with passed final exams found (this is OK if no one has completed yet)\n";
    }
} catch (Exception $e) {
    echo "❌ FAIL: Query error: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 4: Check for duplicate passed results (should not exist)
echo "Test 4: Checking for duplicate passed results...\n";
try {
    $duplicates = DB::table('final_exam_results')
        ->select('enrollment_id', DB::raw('COUNT(*) as count'))
        ->where('is_passing', true)
        ->groupBy('enrollment_id')
        ->having('count', '>', 1)
        ->get();
    
    if ($duplicates->count() === 0) {
        echo "✅ PASS: No duplicate passed results found\n";
    } else {
        echo "⚠️  WARNING: Found {$duplicates->count()} enrollments with multiple passed results:\n";
        foreach ($duplicates as $dup) {
            echo "   - Enrollment ID: {$dup->enrollment_id} has {$dup->count} passed results\n";
        }
    }
} catch (Exception $e) {
    echo "❌ FAIL: Query error: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 5: Check route exists
echo "Test 5: Checking if process-completion route exists...\n";
try {
    $routes = app('router')->getRoutes();
    $found = false;
    
    foreach ($routes as $route) {
        if (str_contains($route->uri(), 'final-exam/process-completion')) {
            $found = true;
            echo "✅ PASS: Route found\n";
            echo "   URI: {$route->uri()}\n";
            echo "   Method: " . implode('|', $route->methods()) . "\n";
            echo "   Action: {$route->getActionName()}\n";
            break;
        }
    }
    
    if (!$found) {
        echo "❌ FAIL: Route not found\n";
    }
} catch (Exception $e) {
    echo "❌ FAIL: Route check error: " . $e->getMessage() . "\n";
}

echo "\n=== Verification Complete ===\n";
