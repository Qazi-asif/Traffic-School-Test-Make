<?php

/**
 * Check and fix enrollment progress for completed final exams
 * Run: php fix-enrollment-progress.php <enrollment_id>
 */

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\UserCourseEnrollment;
use App\Models\FinalExamResult;
use App\Http\Controllers\ProgressController;

$enrollmentId = $argv[1] ?? null;

if (!$enrollmentId) {
    echo "Usage: php fix-enrollment-progress.php <enrollment_id>\n";
    exit(1);
}

echo "=== Fixing Enrollment Progress ===\n\n";

try {
    $enrollment = UserCourseEnrollment::find($enrollmentId);
    
    if (!$enrollment) {
        echo "❌ Enrollment not found\n";
        exit(1);
    }
    
    echo "Enrollment ID: {$enrollment->id}\n";
    echo "User ID: {$enrollment->user_id}\n";
    echo "Current Status: {$enrollment->status}\n";
    echo "Current Progress: {$enrollment->progress_percentage}%\n";
    echo "Final Exam Completed: " . ($enrollment->final_exam_completed ? 'Yes' : 'No') . "\n\n";
    
    // Check for passed final exam result
    $passedResult = FinalExamResult::where('enrollment_id', $enrollmentId)
        ->where('is_passing', true)
        ->first();
    
    if ($passedResult) {
        echo "✅ Found passed final exam result (ID: {$passedResult->id})\n";
        echo "   Score: {$passedResult->final_exam_score}%\n";
        echo "   Completed: {$passedResult->exam_completed_at}\n\n";
        
        // Update enrollment flags
        $enrollment->final_exam_completed = true;
        $enrollment->final_exam_result_id = $passedResult->id;
        $enrollment->save();
        
        echo "Updated enrollment flags\n\n";
        
        // Recalculate progress
        $progressController = new ProgressController();
        $progressController->updateEnrollmentProgressPublic($enrollment);
        
        $enrollment->refresh();
        
        echo "=== AFTER FIX ===\n";
        echo "Status: {$enrollment->status}\n";
        echo "Progress: {$enrollment->progress_percentage}%\n";
        echo "Completed At: " . ($enrollment->completed_at ?? 'Not set') . "\n\n";
        
        if ($enrollment->status === 'completed' && $enrollment->progress_percentage == 100) {
            echo "✅ SUCCESS! Enrollment is now properly completed.\n";
        } else {
            echo "⚠️  Progress updated but may need manual review.\n";
        }
        
    } else {
        echo "❌ No passed final exam result found for this enrollment.\n";
        echo "   Student needs to submit the final exam.\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
