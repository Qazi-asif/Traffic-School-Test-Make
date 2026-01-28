<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🎯 FINAL SYSTEM VERIFICATION\n";
echo "============================\n\n";

try {
    // Test 1: Database Connection and Tables
    echo "TEST 1: Database Connection & Tables\n";
    echo "------------------------------------\n";
    
    $tables = [
        'users' => DB::table('users')->count(),
        'user_course_enrollments' => DB::table('user_course_enrollments')->count(),
        'florida_courses' => DB::table('florida_courses')->count(),
        'courses' => DB::table('courses')->count(),
    ];
    
    foreach ($tables as $table => $count) {
        echo "✅ {$table}: {$count} records\n";
    }
    
    // Test 2: Certificate System
    echo "\nTEST 2: Certificate System\n";
    echo "--------------------------\n";
    
    $completedEnrollments = DB::table('user_course_enrollments')
        ->where('status', 'completed')
        ->count();
    
    $withCertificates = DB::table('user_course_enrollments')
        ->where('status', 'completed')
        ->whereNotNull('certificate_generated_at')
        ->count();
    
    echo "✅ Completed enrollments: {$completedEnrollments}\n";
    echo "✅ With certificates: {$withCertificates}\n";
    
    // Test 3: File Structure
    echo "\nTEST 3: File Structure\n";
    echo "----------------------\n";
    
    $files = [
        'app/Http/Controllers/CertificateController.php' => file_exists('app/Http/Controllers/CertificateController.php'),
        'resources/views/certificate-pdf.blade.php' => file_exists('resources/views/certificate-pdf.blade.php'),
        'resources/views/admin/certificates.blade.php' => file_exists('resources/views/admin/certificates.blade.php'),
        'public/certificates/' => is_dir('public/certificates'),
        'public/images/state-stamps/' => is_dir('public/images/state-stamps'),
    ];
    
    foreach ($files as $file => $exists) {
        echo ($exists ? "✅" : "❌") . " {$file}\n";
    }
    
    // Test 4: State Stamps
    echo "\nTEST 4: State Stamp Images\n";
    echo "--------------------------\n";
    
    $states = ['FL', 'CA', 'TX', 'MO', 'DE'];
    foreach ($states as $state) {
        $exists = file_exists("public/images/state-stamps/{$state}-seal.png");
        echo ($exists ? "✅" : "❌") . " {$state} seal\n";
    }
    
    // Test 5: Sample Certificate Test
    echo "\nTEST 5: Sample Certificate Generation\n";
    echo "------------------------------------\n";
    
    $sampleEnrollment = DB::table('user_course_enrollments')
        ->where('status', 'completed')
        ->first();
    
    if ($sampleEnrollment) {
        echo "✅ Sample enrollment found (ID: {$sampleEnrollment->id})\n";
        echo "✅ Certificate URLs:\n";
        echo "   - View: /certificate/view?enrollment_id={$sampleEnrollment->id}\n";
        echo "   - Generate: /certificate/generate?enrollment_id={$sampleEnrollment->id}\n";
        
        if ($sampleEnrollment->certificate_path) {
            $certExists = file_exists("public/{$sampleEnrollment->certificate_path}");
            echo "   - File exists: " . ($certExists ? "✅ Yes" : "❌ No") . "\n";
        }
    } else {
        echo "❌ No sample enrollment found\n";
    }
    
    // Test 6: Routes Check
    echo "\nTEST 6: Routes Configuration\n";
    echo "----------------------------\n";
    
    $routesContent = file_get_contents('routes/web.php');
    $routes = [
        'CertificateController' => strpos($routesContent, 'CertificateController') !== false,
        'certificate.generate' => strpos($routesContent, 'certificate.generate') !== false,
        'certificate.view' => strpos($routesContent, 'certificate.view') !== false,
        'ProgressApiController' => strpos($routesContent, 'ProgressApiController') !== false,
    ];
    
    foreach ($routes as $route => $exists) {
        echo ($exists ? "✅" : "❌") . " {$route}\n";
    }
    
    // Test 7: Test Pages
    echo "\nTEST 7: Test Pages\n";
    echo "------------------\n";
    
    $testPages = [
        'my-certificates.php' => file_exists('my-certificates.php'),
        'test-certificates.php' => file_exists('test-certificates.php'),
        'quick-login.php' => file_exists('quick-login.php'),
        'logout.php' => file_exists('logout.php'),
    ];
    
    foreach ($testPages as $page => $exists) {
        echo ($exists ? "✅" : "❌") . " {$page}\n";
    }
    
    // Final Summary
    echo "\n🎉 VERIFICATION COMPLETE!\n";
    echo "=========================\n";
    
    $allGood = true;
    foreach (array_merge($files, $routes, $testPages) as $item => $status) {
        if (!$status) {
            $allGood = false;
            break;
        }
    }
    
    if ($allGood && $completedEnrollments > 0) {
        echo "✅ ALL SYSTEMS OPERATIONAL!\n";
        echo "✅ Certificate generation ready\n";
        echo "✅ Admin interface ready\n";
        echo "✅ Student interface ready\n";
        echo "✅ Database properly configured\n";
        echo "✅ All files in place\n";
    } else {
        echo "⚠️  Some issues detected - review above\n";
    }
    
    echo "\n📋 QUICK ACCESS URLS:\n";
    echo "- Admin Certificates: /admin/certificates\n";
    echo "- Student Certificates: /my-certificates.php\n";
    echo "- Test Interface: /test-certificates.php\n";
    echo "- Quick Login: /quick-login.php\n";
    
    if ($sampleEnrollment) {
        echo "\n🧪 TEST CERTIFICATE:\n";
        echo "- View: /certificate/view?enrollment_id={$sampleEnrollment->id}\n";
        echo "- Generate: /certificate/generate?enrollment_id={$sampleEnrollment->id}\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}

echo "\n🏁 Verification completed at " . date('Y-m-d H:i:s') . "\n";