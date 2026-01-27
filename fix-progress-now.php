<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\UserCourseEnrollment;
use App\Http\Controllers\ProgressController;

$enrollmentId = $argv[1] ?? null;

if (!$enrollmentId) {
    echo "Usage: php fix-progress-now.php <enrollment_id>\n";
    exit(1);
}

echo "=== Fixing Progress for Enrollment $enrollmentId ===\n\n";

try {
    $enrollment = UserCourseEnrollment::find($enrollmentId);
    
    if (!$enrollment) {
        echo "❌ Enrollment not found\n";
        exit(1);
    }
    
    echo "BEFORE:\n";
    echo "  Status: {$enrollment->status}\n";
    echo "  Progress: {$enrollment->progress_percentage}%\n";
    echo "  Final Exam Completed: " . ($enrollment->final_exam_completed ? 'Yes' : 'No') . "\n\n";
    
    // Check for passed result
    $passedResult = \DB::table('final_exam_results')
        ->where('enrollment_id', $enrollmentId)
        ->where('passed', true)
        ->first();
    
    if ($passedResult) {
        echo "✅ Found passed result (Score: {$passedResult->score}%)\n\n";
        
        // Update flags
        $enrollment->final_exam_completed = true;
        $enrollment->final_exam_result_id = $passedResult->id;
        $enrollment->save();
        
        // Recalculate progress
        $progressController = new ProgressController();
        $progressController->updateEnrollmentProgressPublic($enrollment);
        
        $enrollment->refresh();
        
        echo "AFTER:\n";
        echo "  Status: {$enrollment->status}\n";
        echo "  Progress: {$enrollment->progress_percentage}%\n";
        echo "  Completed At: " . ($enrollment->completed_at ?? 'Not set') . "\n\n";
        
        if ($enrollment->status === 'completed' && $enrollment->progress_percentage == 100) {
            echo "✅ SUCCESS! Progress is now 100%\n";
        } else {
            echo "⚠️  Still not 100%. Check logs.\n";
        }
    } else {
        echo "❌ No passed result found\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
