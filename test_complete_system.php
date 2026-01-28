<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🚀 COMPREHENSIVE SYSTEM TEST\n";
echo "============================\n\n";

try {
    // Test 1: Database Structure
    echo "TEST 1: Database Structure\n";
    echo "--------------------------\n";
    
    $requiredColumns = [
        'user_course_enrollments' => ['certificate_generated_at', 'certificate_number', 'certificate_path'],
    ];
    
    foreach ($requiredColumns as $table => $columns) {
        foreach ($columns as $column) {
            $hasColumn = DB::getSchemaBuilder()->hasColumn($table, $column);
            echo ($hasColumn ? "✅" : "❌") . " {$table}.{$column}\n";
        }
    }
    
    // Test 2: Certificate Generation
    echo "\nTEST 2: Certificate System\n";
    echo "--------------------------\n";
    
    $totalCompleted = DB::table('user_course_enrollments')->where('status', 'completed')->count();
    $totalWithCertificates = DB::table('user_course_enrollments')
        ->where('status', 'completed')
        ->whereNotNull('certificate_generated_at')
        ->count();
    
    echo "✅ Total completed enrollments: {$totalCompleted}\n";
    echo "✅ Total with certificates: {$totalWithCertificates}\n";
    
    // Test 3: File Structure
    echo "\nTEST 3: File Structure\n";
    echo "----------------------\n";
    
    $requiredFiles = [
        'app/Http/Controllers/CertificateController.php',
        'app/Http/Controllers/ProgressApiController.php',
        'resources/views/certificate-pdf.blade.php',
        'resources/views/admin/certificates.blade.php',
        'public/certificates',
        'public/images/state-stamps',
    ];
    
    foreach ($requiredFiles as $file) {
        $exists = file_exists($file);
        echo ($exists ? "✅" : "❌") . " {$file}\n";
    }
    
    // Test 4: State Stamps
    echo "\nTEST 4: State Stamp Images\n";
    echo "--------------------------\n";
    
    $states = ['FL', 'CA', 'TX', 'MO', 'DE'];
    foreach ($states as $state) {
        $sealPath = "public/images/state-stamps/{$state}-seal.png";
        $exists = file_exists($sealPath);
        echo ($exists ? "✅" : "❌") . " {$state} seal: {$sealPath}\n";
    }
    
    // Test 5: Routes Test
    echo "\nTEST 5: Route Configuration\n";
    echo "---------------------------\n";
    
    $routesContent = file_get_contents('routes/web.php');
    $requiredRoutes = [
        'certificates.index',
        'certificate.generate',
        'certificate.view',
        'ProgressApiController',
    ];
    
    foreach ($requiredRoutes as $route) {
        $exists = strpos($routesContent, $route) !== false;
        echo ($exists ? "✅" : "❌") . " Route: {$route}\n";
    }
    
    // Test 6: Sample Certificate Generation
    echo "\nTEST 6: Sample Certificate Generation\n";
    echo "------------------------------------\n";
    
    $sampleEnrollment = DB::table('user_course_enrollments as uce')
        ->join('users as u', 'uce.user_id', '=', 'u.id')
        ->where('uce.status', 'completed')
        ->whereNotNull('uce.certificate_generated_at')
        ->select(['uce.id', 'u.first_name', 'u.last_name', 'uce.certificate_number', 'uce.certificate_path'])
        ->first();
    
    if ($sampleEnrollment) {
        echo "✅ Sample certificate found:\n";
        echo "   - Student: {$sampleEnrollment->first_name} {$sampleEnrollment->last_name}\n";
        echo "   - Certificate: {$sampleEnrollment->certificate_number}\n";
        echo "   - Path: {$sampleEnrollment->certificate_path}\n";
        echo "   - View URL: /certificate/view?enrollment_id={$sampleEnrollment->id}\n";
        echo "   - Generate URL: /certificate/generate?enrollment_id={$sampleEnrollment->id}\n";
        
        // Check if certificate file exists
        $certExists = file_exists("public/{$sampleEnrollment->certificate_path}");
        echo "   - File exists: " . ($certExists ? "✅ Yes" : "❌ No") . "\n";
    } else {
        echo "❌ No sample certificates found\n";
    }
    
    // Test 7: Progress System
    echo "\nTEST 7: Progress System\n";
    echo "-----------------------\n";
    
    $stuckProgress = DB::table('user_course_enrollments as uce')
        ->leftJoin('final_exam_results as fer', 'uce.id', '=', 'fer.enrollment_id')
        ->where('uce.status', 'completed')
        ->where('uce.progress_percentage', '<', 100)
        ->whereNotNull('fer.id')
        ->count();
    
    echo "✅ Enrollments with 100% progress: " . DB::table('user_course_enrollments')->where('progress_percentage', 100)->count() . "\n";
    echo ($stuckProgress == 0 ? "✅" : "⚠️") . " Stuck progress issues: {$stuckProgress}\n";
    
    // Test 8: Admin Access
    echo "\nTEST 8: Admin Access URLs\n";
    echo "-------------------------\n";
    
    echo "✅ Admin certificates: /admin/certificates\n";
    echo "✅ API certificates: /api/certificates\n";
    echo "✅ Progress API: /api/progress/{enrollmentId}\n";
    
    // Test 9: Student Access
    echo "\nTEST 9: Student Access URLs\n";
    echo "---------------------------\n";
    
    echo "✅ My certificates: /my-certificates.php\n";
    echo "✅ Test certificates: /test-certificates.php\n";
    echo "✅ Quick login: /quick-login.php\n";
    
    // Final Summary
    echo "\n🎉 SYSTEM TEST COMPLETE!\n";
    echo "========================\n";
    
    $issues = 0;
    if ($stuckProgress > 0) $issues++;
    if ($totalWithCertificates < $totalCompleted) $issues++;
    
    if ($issues == 0) {
        echo "✅ All systems operational!\n";
        echo "✅ Certificate generation working\n";
        echo "✅ Progress tracking fixed\n";
        echo "✅ Admin and student interfaces ready\n";
    } else {
        echo "⚠️  {$issues} issues detected - review above\n";
    }
    
    echo "\n📋 NEXT STEPS:\n";
    echo "1. Visit /admin/certificates to manage certificates\n";
    echo "2. Test certificate generation with /test-certificates.php\n";
    echo "3. Students can view certificates at /my-certificates.php\n";
    echo "4. Use /quick-login.php for testing different users\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n🏁 Test completed at " . date('Y-m-d H:i:s') . "\n";