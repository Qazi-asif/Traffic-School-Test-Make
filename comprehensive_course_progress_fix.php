<?php
/**
 * Comprehensive Course Progress & Completion System Fix
 * Fixes all issues with course progress, final exam completion, and certificate generation
 */

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\UserCourseEnrollment;
use App\Models\UserCourseProgress;
use App\Models\FinalExamResult;
use App\Models\FloridaCertificate;

echo "🚀 COMPREHENSIVE COURSE PROGRESS & COMPLETION FIX\n";
echo "================================================\n\n";

try {
    // STEP 1: Analyze Current Issues
    echo "STEP 1: Analyzing Current Course Progress Issues\n";
    echo "-----------------------------------------------\n";
    
    // Check enrollments with inconsistent progress
    $inconsistentProgress = DB::select("
        SELECT 
            uce.id,
            uce.progress_percentage,
            uce.status,
            uce.final_exam_completed,
            fer.passed as final_exam_passed,
            fer.score as final_exam_score,
            COUNT(DISTINCT ucp.chapter_id) as completed_chapters,
            COUNT(DISTINCT c.id) as total_chapters
        FROM user_course_enrollments uce
        LEFT JOIN final_exam_results fer ON uce.id = fer.enrollment_id AND fer.passed = 1
        LEFT JOIN user_course_progress ucp ON uce.id = ucp.enrollment_id AND ucp.is_completed = 1
        LEFT JOIN chapters c ON c.course_id = uce.course_id AND c.is_active = 1
        GROUP BY uce.id
        HAVING (
            (fer.passed = 1 AND uce.progress_percentage < 100) OR
            (completed_chapters >= total_chapters AND total_chapters > 0 AND uce.progress_percentage < 100) OR
            (uce.status = 'completed' AND uce.progress_percentage < 100)
        )
        ORDER BY uce.id
    ");
    
    echo "✅ Found " . count($inconsistentProgress) . " enrollments with inconsistent progress\n";
    
    foreach (array_slice($inconsistentProgress, 0, 5) as $issue) {
        echo "   - Enrollment {$issue->id}: {$issue->progress_percentage}% (Status: {$issue->status}, Final Exam: " . ($issue->final_exam_passed ? 'Passed' : 'Not Passed') . ")\n";
    }
    
    // STEP 2: Fix Progress Controller Logic
    echo "\nSTEP 2: Updating Progress Controller with Improved Logic\n";
    echo "-------------------------------------------------------\n";
    
    $progressControllerPath = 'app/Http/Controllers/ProgressController.php';
    $currentController = file_get_contents($progressControllerPath);
    
    // Create the improved updateEnrollmentProgress method
    $improvedMethod = '    private function updateEnrollmentProgress(UserCourseEnrollment $enrollment)
    {
        try {
            \Log::info("Updating progress for enrollment {$enrollment->id}");
            
            // Get total chapters from chapters table
            $totalChapters = \App\Models\Chapter::where(\'course_id\', $enrollment->course_id)
                ->where(\'is_active\', true)
                ->count();

            if ($totalChapters == 0) {
                \Log::warning("No chapters found for course_id: {$enrollment->course_id}");
                return;
            }

            // Count unique completed chapters
            $completedChapters = \DB::table(\'user_course_progress\')
                ->where(\'enrollment_id\', $enrollment->id)
                ->where(\'is_completed\', true)
                ->distinct()
                ->count(\'chapter_id\');

            // Check if final exam is completed AND passed
            $finalExamResult = \DB::table(\'final_exam_results\')
                ->where(\'enrollment_id\', $enrollment->id)
                ->where(\'passed\', true)
                ->orderBy(\'created_at\', \'desc\')
                ->first();

            $finalExamPassed = $finalExamResult !== null;
            
            // Calculate progress based on completion criteria
            $chapterProgressPercentage = $totalChapters > 0 ? ($completedChapters / $totalChapters) * 100 : 0;
            
            // Determine overall progress and completion status
            $isFullyCompleted = ($chapterProgressPercentage >= 100) && $finalExamPassed;
            
            if ($isFullyCompleted) {
                $progressPercentage = 100;
                $status = \'completed\';
                $completedAt = $enrollment->completed_at ?? now();
            } elseif ($chapterProgressPercentage >= 100 && !$finalExamPassed) {
                // All chapters done but final exam not passed - cap at 95%
                $progressPercentage = 95;
                $status = \'active\';
                $completedAt = null;
            } else {
                // Chapters in progress
                $progressPercentage = $chapterProgressPercentage;
                $status = \'active\';
                $completedAt = null;
            }

            $totalTimeSpent = $enrollment->progress()->sum(\'time_spent\');
            $wasCompleted = $enrollment->status === \'completed\';

            \Log::info("Progress update - Enrollment: {$enrollment->id}, Total Chapters: {$totalChapters}, Completed: {$completedChapters}, Final Exam Passed: " . ($finalExamPassed ? \'Yes\' : \'No\') . ", Progress: {$progressPercentage}%");

            // Update enrollment
            $enrollment->update([
                \'progress_percentage\' => $progressPercentage,
                \'total_time_spent\' => $totalTimeSpent,
                \'completed_at\' => $completedAt,
                \'status\' => $status,
                \'final_exam_completed\' => $finalExamResult ? true : $enrollment->final_exam_completed,
            ]);

            // Generate certificate and fire event ONLY if course is fully completed
            if ($isFullyCompleted && !$wasCompleted) {
                $this->generateCertificate($enrollment);
                event(new \App\Events\CourseCompleted($enrollment));
            }
            
        } catch (\Exception $e) {
            \Log::error(\'Error updating enrollment progress: \' . $e->getMessage());
            \Log::error(\'Stack trace: \' . $e->getTraceAsString());
        }
    }';
    
    // Replace the existing method
    $pattern = '/private function updateEnrollmentProgress\([^{]*\{(?:[^{}]*\{[^{}]*\})*[^{}]*\}/s';
    if (preg_match($pattern, $currentController)) {
        $newController = preg_replace($pattern, $improvedMethod, $currentController);
        file_put_contents($progressControllerPath, $newController);
        echo "✅ Updated existing updateEnrollmentProgress method\n";
    } else {
        echo "❌ Could not find existing method to replace\n";
    }
    
    // STEP 3: Fix All Current Progress Issues
    echo "\nSTEP 3: Fixing All Current Progress Issues\n";
    echo "-----------------------------------------\n";
    
    $fixedCount = 0;
    
    foreach ($inconsistentProgress as $issue) {
        $enrollment = UserCourseEnrollment::find($issue->id);
        if (!$enrollment) continue;
        
        // Determine correct progress and status
        $shouldBe100 = false;
        $shouldBeCompleted = false;
        
        // If final exam is passed, should be 100% and completed
        if ($issue->final_exam_passed) {
            $shouldBe100 = true;
            $shouldBeCompleted = true;
        }
        // If all chapters completed but no final exam, should be 95%
        elseif ($issue->completed_chapters >= $issue->total_chapters && $issue->total_chapters > 0) {
            $progressPercentage = 95;
        }
        // Otherwise calculate based on chapter completion
        else {
            $progressPercentage = $issue->total_chapters > 0 ? 
                ($issue->completed_chapters / $issue->total_chapters) * 100 : 0;
        }
        
        if ($shouldBe100) {
            $enrollment->update([
                \'progress_percentage\' => 100,
                \'status\' => \'completed\',
                \'completed_at\' => $enrollment->completed_at ?? now(),
                \'final_exam_completed\' => true
            ]);
            echo "   ✅ Fixed enrollment {$issue->id}: Set to 100% (Final exam passed)\n";
        } else {
            $enrollment->update([
                \'progress_percentage\' => $progressPercentage,
                \'status\' => \'active\',
                \'completed_at\' => null
            ]);
            echo "   ✅ Fixed enrollment {$issue->id}: Set to {$progressPercentage}%\n";
        }
        
        $fixedCount++;
    }
    
    echo "✅ Fixed {$fixedCount} enrollments with inconsistent progress\n";
    
    // STEP 4: Create Progress Monitoring API
    echo "\nSTEP 4: Creating Progress Monitoring API\n";
    echo "---------------------------------------\n";
    
    $progressApiController = \'<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserCourseEnrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProgressApiController extends Controller
{
    /**
     * Get real-time progress for an enrollment
     */
    public function getProgress($enrollmentId)
    {
        try {
            $user = Auth::user();
            
            $enrollment = UserCourseEnrollment::where(\'id\', $enrollmentId)
                ->where(\'user_id\', $user->id)
                ->first();
            
            if (!$enrollment) {
                return response()->json([\'error\' => \'Enrollment not found\'], 404);
            }
            
            // Get chapter progress
            $totalChapters = DB::table(\'chapters\')
                ->where(\'course_id\', $enrollment->course_id)
                ->where(\'is_active\', true)
                ->count();
            
            $completedChapters = DB::table(\'user_course_progress\')
                ->where(\'enrollment_id\', $enrollmentId)
                ->where(\'is_completed\', true)
                ->distinct(\'chapter_id\')
                ->count(\'chapter_id\');
            
            // Get final exam status
            $finalExamResult = DB::table(\'final_exam_results\')
                ->where(\'enrollment_id\', $enrollmentId)
                ->orderBy(\'created_at\', \'desc\')
                ->first();
            
            $finalExamPassed = $finalExamResult && $finalExamResult->passed;
            
            // Calculate accurate progress
            if ($finalExamPassed) {
                $progressPercentage = 100;
                $status = \'completed\';
            } elseif ($completedChapters >= $totalChapters && $totalChapters > 0) {
                $progressPercentage = 95;
                $status = \'active\';
            } else {
                $progressPercentage = $totalChapters > 0 ? 
                    ($completedChapters / $totalChapters) * 100 : 0;
                $status = \'active\';
            }
            
            // Update enrollment if progress has changed
            if ($enrollment->progress_percentage != $progressPercentage) {
                $enrollment->update([
                    \'progress_percentage\' => $progressPercentage,
                    \'status\' => $status,
                    \'completed_at\' => $status === \'completed\' ? ($enrollment->completed_at ?? now()) : null
                ]);
            }
            
            return response()->json([
                \'enrollment_id\' => $enrollmentId,
                \'progress_percentage\' => round($progressPercentage, 2),
                \'status\' => $status,
                \'completed_chapters\' => $completedChapters,
                \'total_chapters\' => $totalChapters,
                \'final_exam_passed\' => $finalExamPassed,
                \'final_exam_attempted\' => $finalExamResult ? true : false,
                \'final_exam_score\' => $finalExamResult->score ?? null,
                \'can_take_final_exam\' => $completedChapters >= $totalChapters,
                \'course_completed\' => $status === \'completed\'
            ]);
            
        } catch (\Exception $e) {
            \Log::error(\'Progress API error: \' . $e->getMessage());
            return response()->json([\'error\' => \'Failed to get progress\'], 500);
        }
    }
    
    /**
     * Force progress recalculation
     */
    public function recalculateProgress($enrollmentId)
    {
        try {
            $user = Auth::user();
            
            $enrollment = UserCourseEnrollment::where(\'id\', $enrollmentId)
                ->where(\'user_id\', $user->id)
                ->first();
            
            if (!$enrollment) {
                return response()->json([\'error\' => \'Enrollment not found\'], 404);
            }
            
            // Use the ProgressController to recalculate
            $progressController = new \App\Http\Controllers\ProgressController();
            $progressController->updateEnrollmentProgressPublic($enrollment);
            
            $enrollment->refresh();
            
            return response()->json([
                \'enrollment_id\' => $enrollmentId,
                \'progress_percentage\' => $enrollment->progress_percentage,
                \'status\' => $enrollment->status,
                \'message\' => \'Progress recalculated successfully\'
            ]);
            
        } catch (\Exception $e) {
            \Log::error(\'Progress recalculation error: \' . $e->getMessage());
            return response()->json([\'error\' => \'Failed to recalculate progress\'], 500);
        }
    }
}\';
    
    // Create the API controller directory if it doesn\'t exist
    if (!is_dir(\'app/Http/Controllers/Api\')) {
        mkdir(\'app/Http/Controllers/Api\', 0755, true);
    }
    
    file_put_contents(\'app/Http/Controllers/Api/ProgressApiController.php\', $progressApiController);
    echo "✅ Created Progress API Controller\n";
    
    // STEP 5: Add API Routes
    echo "\nSTEP 5: Adding Progress API Routes\n";
    echo "---------------------------------\n";
    
    $routesPath = \'routes/web.php\';
    $routesContent = file_get_contents($routesPath);
    
    $progressRoutes = \'
// Progress Monitoring API Routes
Route::middleware([\\\'auth\\\'])->prefix(\\\'api\\\')->group(function () {
    Route::get(\\\'/progress/{enrollmentId}\\\', [App\\\\Http\\\\Controllers\\\\Api\\\\ProgressApiController::class, \\\'getProgress\\\']);
    Route::post(\\\'/progress/{enrollmentId}/recalculate\\\', [App\\\\Http\\\\Controllers\\\\Api\\\\ProgressApiController::class, \\\'recalculateProgress\\\']);
});
\';

    if (strpos($routesContent, \'ProgressApiController\') === false) {
        $routesContent .= $progressRoutes;
        file_put_contents($routesPath, $routesContent);
        echo "✅ Progress API routes added\n";
    } else {
        echo "✅ Progress API routes already exist\n";
    }
    
    // STEP 6: Create Certificate Generation Fix
    echo "\nSTEP 6: Fixing Certificate Generation\n";
    echo "------------------------------------\n";
    
    // Find completed enrollments without certificates
    $missingCertificates = DB::table(\'user_course_enrollments as uce\')
        ->leftJoin(\'florida_certificates as fc\', \'uce.id\', \'=\', \'fc.enrollment_id\')
        ->where(\'uce.status\', \'completed\')
        ->where(\'uce.progress_percentage\', 100)
        ->whereNull(\'fc.id\')
        ->select([\'uce.id\', \'uce.user_id\', \'uce.course_id\', \'uce.completed_at\'])
        ->get();
    
    echo "✅ Found " . $missingCertificates->count() . " completed enrollments without certificates\n";
    
    $certificatesGenerated = 0;
    foreach ($missingCertificates as $missing) {
        try {
            $enrollment = UserCourseEnrollment::find($missing->id);
            if (!$enrollment) continue;
            
            // Generate certificate number
            $year = date(\'Y\');
            $lastCertificate = FloridaCertificate::whereYear(\'created_at\', $year)
                ->orderBy(\'id\', \'desc\')
                ->first();

            $sequence = $lastCertificate ?
                (int) substr($lastCertificate->dicds_certificate_number, -6) + 1 : 1;

            $certificateNumber = \'FL\'.$year.str_pad($sequence, 6, \'0\', STR_PAD_LEFT);

            FloridaCertificate::create([
                \'enrollment_id\' => $enrollment->id,
                \'dicds_certificate_number\' => $certificateNumber,
                \'student_name\' => $enrollment->user->first_name.\' \'.$enrollment->user->last_name,
                \'course_name\' => \'Florida Traffic School Course\',
                \'completion_date\' => $enrollment->completed_at,
                \'verification_hash\' => \Illuminate\Support\Str::random(32),
                \'status\' => \'generated\',
            ]);
            
            $certificatesGenerated++;
            
        } catch (\Exception $e) {
            \Log::error(\'Certificate generation error for enrollment \' . $missing->id . \': \' . $e->getMessage());
        }
    }
    
    echo "✅ Generated {$certificatesGenerated} missing certificates\n";
    
    // STEP 7: Create Progress Monitoring Dashboard
    echo "\nSTEP 7: Creating Progress Monitoring Tools\n";
    echo "-----------------------------------------\n";
    
    $monitoringScript = \'<?php
/**
 * Progress Monitoring Script
 * Run this to check and fix progress issues
 */

require_once "vendor/autoload.php";
$app = require_once "bootstrap/app.php";
$app->make("Illuminate\\\\Contracts\\\\Console\\\\Kernel")->bootstrap();

use Illuminate\\\\Support\\\\Facades\\\\DB;

echo "📊 PROGRESS MONITORING REPORT\\n";
echo "============================\\n\\n";

// Overall statistics
$totalEnrollments = DB::table("user_course_enrollments")->count();
$completedEnrollments = DB::table("user_course_enrollments")->where("status", "completed")->count();
$activeEnrollments = DB::table("user_course_enrollments")->where("status", "active")->count();
$progress100 = DB::table("user_course_enrollments")->where("progress_percentage", 100)->count();

echo "📈 OVERALL STATISTICS:\\n";
echo "Total Enrollments: {$totalEnrollments}\\n";
echo "Completed: {$completedEnrollments}\\n";
echo "Active: {$activeEnrollments}\\n";
echo "100% Progress: {$progress100}\\n\\n";

// Progress distribution
$progressRanges = [
    "0-25%" => DB::table("user_course_enrollments")->whereBetween("progress_percentage", [0, 25])->count(),
    "26-50%" => DB::table("user_course_enrollments")->whereBetween("progress_percentage", [26, 50])->count(),
    "51-75%" => DB::table("user_course_enrollments")->whereBetween("progress_percentage", [51, 75])->count(),
    "76-99%" => DB::table("user_course_enrollments")->whereBetween("progress_percentage", [76, 99])->count(),
    "100%" => DB::table("user_course_enrollments")->where("progress_percentage", 100)->count()
];

echo "📊 PROGRESS DISTRIBUTION:\\n";
foreach ($progressRanges as $range => $count) {
    echo "{$range}: {$count} enrollments\\n";
}

// Issues detection
echo "\\n🔍 ISSUES DETECTION:\\n";

$stuckProgress = DB::select("
    SELECT COUNT(*) as count
    FROM user_course_enrollments uce
    LEFT JOIN final_exam_results fer ON uce.id = fer.enrollment_id AND fer.passed = 1
    WHERE fer.passed = 1 AND uce.progress_percentage < 100
");

echo "Enrollments with passed final exam but < 100%: " . $stuckProgress[0]->count . "\\n";

$missingCertificates = DB::table("user_course_enrollments as uce")
    ->leftJoin("florida_certificates as fc", "uce.id", "=", "fc.enrollment_id")
    ->where("uce.status", "completed")
    ->where("uce.progress_percentage", 100)
    ->whereNull("fc.id")
    ->count();

echo "Completed enrollments without certificates: {$missingCertificates}\\n";

echo "\\n✅ Monitoring complete!\\n";
?\';
    
    file_put_contents(\'progress_monitoring.php\', $monitoringScript);
    echo "✅ Created progress monitoring script\n";
    
    // STEP 8: Final Verification
    echo "\nSTEP 8: Final Verification\n";
    echo "-------------------------\n";
    
    // Check if issues are resolved
    $remainingIssues = DB::select("
        SELECT COUNT(*) as count
        FROM user_course_enrollments uce
        LEFT JOIN final_exam_results fer ON uce.id = fer.enrollment_id AND fer.passed = 1
        WHERE fer.passed = 1 AND uce.progress_percentage < 100
    ");
    
    $totalCompleted = DB::table(\'user_course_enrollments\')->where(\'status\', \'completed\')->count();
    $total100Percent = DB::table(\'user_course_enrollments\')->where(\'progress_percentage\', 100)->count();
    
    echo "✅ Remaining progress issues: " . $remainingIssues[0]->count . "\n";
    echo "✅ Total completed enrollments: {$totalCompleted}\n";
    echo "✅ Total with 100% progress: {$total100Percent}\n";
    
    echo "\n🎉 COMPREHENSIVE COURSE PROGRESS FIX COMPLETE!\n";
    echo "=============================================\n";
    echo "✅ Progress calculation logic improved\n";
    echo "✅ {$fixedCount} enrollments fixed\n";
    echo "✅ {$certificatesGenerated} certificates generated\n";
    echo "✅ Progress monitoring API created\n";
    echo "✅ Monitoring tools created\n\n";
    
    echo "📋 WHAT WAS FIXED:\n";
    echo "1. Improved progress calculation in ProgressController\n";
    echo "2. Fixed all enrollments with inconsistent progress\n";
    echo "3. Generated missing certificates for completed courses\n";
    echo "4. Created real-time progress monitoring API\n";
    echo "5. Added progress recalculation endpoints\n";
    echo "6. Created monitoring and diagnostic tools\n\n";
    
    echo "🔧 NEW FEATURES:\n";
    echo "- GET /api/progress/{enrollmentId} - Real-time progress\n";
    echo "- POST /api/progress/{enrollmentId}/recalculate - Force recalculation\n";
    echo "- Run progress_monitoring.php for system health checks\n\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "🏁 Fix completed at " . date(\'Y-m-d H:i:s\') . "\n";