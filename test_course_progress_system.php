<?php
/**
 * Test Course Progress System
 * Verify that the course progress and completion system is working correctly
 */

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\UserCourseEnrollment;

echo "🧪 Testing Course Progress & Completion System\n";
echo "=============================================\n\n";

try {
    // Test 1: Check Progress Calculation Logic
    echo "TEST 1: Progress Calculation Logic\n";
    echo "---------------------------------\n";
    
    $sampleEnrollments = UserCourseEnrollment::with(['user', 'progress'])
        ->limit(5)
        ->get();
    
    foreach ($sampleEnrollments as $enrollment) {
        $totalChapters = DB::table('chapters')
            ->where('course_id', $enrollment->course_id)
            ->where('is_active', true)
            ->count();
        
        $completedChapters = $enrollment->progress()
            ->where('is_completed', true)
            ->distinct('chapter_id')
            ->count('chapter_id');
        
        $finalExamPassed = DB::table('final_exam_results')
            ->where('enrollment_id', $enrollment->id)
            ->where('passed', true)
            ->exists();
        
        $expectedProgress = 0;
        if ($finalExamPassed) {
            $expectedProgress = 100;
        } elseif ($totalChapters > 0 && $completedChapters >= $totalChapters) {
            $expectedProgress = 95;
        } elseif ($totalChapters > 0) {
            $expectedProgress = ($completedChapters / $totalChapters) * 100;
        }
        
        $progressMatch = abs($enrollment->progress_percentage - $expectedProgress) < 1;
        
        echo "   Enrollment {$enrollment->id}: ";
        echo "Chapters {$completedChapters}/{$totalChapters}, ";
        echo "Final Exam: " . ($finalExamPassed ? 'Passed' : 'Not Passed') . ", ";
        echo "Progress: {$enrollment->progress_percentage}% ";
        echo "(" . ($progressMatch ? '✅ Correct' : '❌ Expected ' . round($expectedProgress, 1) . '%') . ")\n";
    }
    
    // Test 2: API Endpoints
    echo "\nTEST 2: API Endpoints\n";
    echo "--------------------\n";
    
    $apiControllerExists = file_exists('app/Http/Controllers/Api/ProgressApiController.php');
    echo "   Progress API Controller: " . ($apiControllerExists ? '✅ Exists' : '❌ Missing') . "\n";
    
    $routesContent = file_get_contents('routes/web.php');
    $apiRoutesExist = strpos($routesContent, 'ProgressApiController') !== false;
    echo "   API Routes: " . ($apiRoutesExist ? '✅ Configured' : '❌ Missing') . "\n";
    
    // Test 3: Certificate Generation
    echo "\nTEST 3: Certificate Generation\n";
    echo "-----------------------------\n";
    
    $completedWithoutCerts = DB::table('user_course_enrollments as uce')
        ->leftJoin('florida_certificates as fc', 'uce.id', '=', 'fc.enrollment_id')
        ->where('uce.status', 'completed')
        ->where('uce.progress_percentage', 100)
        ->whereNull('fc.id')
        ->count();
    
    echo "   Completed enrollments without certificates: {$completedWithoutCerts}\n";
    
    $totalCertificates = DB::table('florida_certificates')->count();
    echo "   Total certificates generated: {$totalCertificates}\n";
    
    // Test 4: Progress Controller Method
    echo "\nTEST 4: Progress Controller Method\n";
    echo "---------------------------------\n";
    
    $controllerContent = file_get_contents('app/Http/Controllers/ProgressController.php');
    $hasImprovedMethod = strpos($controllerContent, 'final_exam_passed') !== false;
    echo "   Improved progress method: " . ($hasImprovedMethod ? '✅ Updated' : '❌ Not Updated') . "\n";
    
    // Test 5: System Statistics
    echo "\nTEST 5: System Statistics\n";
    echo "------------------------\n";
    
    $stats = [
        'Total Enrollments' => DB::table('user_course_enrollments')->count(),
        'Active Enrollments' => DB::table('user_course_enrollments')->where('status', 'active')->count(),
        'Completed Enrollments' => DB::table('user_course_enrollments')->where('status', 'completed')->count(),
        '100% Progress' => DB::table('user_course_enrollments')->where('progress_percentage', 100)->count(),
        'Final Exams Passed' => DB::table('final_exam_results')->where('passed', true)->count(),
        'Certificates Generated' => DB::table('florida_certificates')->count()
    ];
    
    foreach ($stats as $label => $count) {
        echo "   {$label}: {$count}\n";
    }
    
    // Test 6: Progress Consistency Check
    echo "\nTEST 6: Progress Consistency Check\n";
    echo "---------------------------------\n";
    
    $inconsistentProgress = DB::select("
        SELECT COUNT(*) as count
        FROM user_course_enrollments uce
        LEFT JOIN final_exam_results fer ON uce.id = fer.enrollment_id AND fer.passed = 1
        WHERE fer.passed = 1 AND uce.progress_percentage < 100
    ");
    
    $inconsistentCount = $inconsistentProgress[0]->count;
    echo "   Enrollments with passed final exam but < 100% progress: {$inconsistentCount}\n";
    
    if ($inconsistentCount == 0) {
        echo "   ✅ All progress calculations are consistent\n";
    } else {
        echo "   ❌ Found {$inconsistentCount} inconsistent progress calculations\n";
    }
    
    // Overall Assessment
    echo "\n🎯 OVERALL ASSESSMENT\n";
    echo "====================\n";
    
    $issues = [];
    
    if (!$apiControllerExists) $issues[] = "Missing Progress API Controller";
    if (!$apiRoutesExist) $issues[] = "Missing API Routes";
    if (!$hasImprovedMethod) $issues[] = "Progress Controller not updated";
    if ($inconsistentCount > 0) $issues[] = "{$inconsistentCount} inconsistent progress calculations";
    if ($completedWithoutCerts > 0) $issues[] = "{$completedWithoutCerts} missing certificates";
    
    if (empty($issues)) {
        echo "✅ Course Progress & Completion System: WORKING CORRECTLY\n";
        echo "✅ All tests passed successfully\n";
    } else {
        echo "⚠️  Course Progress & Completion System: NEEDS ATTENTION\n";
        echo "Issues found:\n";
        foreach ($issues as $issue) {
            echo "   - {$issue}\n";
        }
    }
    
    echo "\n📋 NEXT STEPS:\n";
    echo "1. Test the progress API endpoints in your browser\n";
    echo "2. Verify certificate generation for completed courses\n";
    echo "3. Monitor progress calculations for new enrollments\n";
    echo "4. Run progress_monitoring.php for ongoing health checks\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n🏁 Testing completed at " . date('Y-m-d H:i:s') . "\n";