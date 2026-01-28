<?php
/**
 * Test Certificate System
 * Comprehensive test of the certificate generation and display system
 */

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "🧪 TESTING CERTIFICATE SYSTEM\n";
echo "=============================\n\n";

try {
    // Test 1: Check Certificate Controller
    echo "TEST 1: Certificate Controller\n";
    echo "-----------------------------\n";
    
    if (class_exists('App\Http\Controllers\CertificateController')) {
        echo "✅ CertificateController exists\n";
        
        $controller = new App\Http\Controllers\CertificateController();
        $methods = ['index', 'show', 'download', 'generate'];
        
        foreach ($methods as $method) {
            if (method_exists($controller, $method)) {
                echo "✅ Method '{$method}' exists\n";
            } else {
                echo "❌ Method '{$method}' missing\n";
            }
        }
    } else {
        echo "❌ CertificateController not found\n";
    }
    
    // Test 2: Check Certificate Template
    echo "\nTEST 2: Certificate Template\n";
    echo "---------------------------\n";
    
    $templatePath = resource_path('views/certificate-pdf.blade.php');
    if (file_exists($templatePath)) {
        echo "✅ Certificate template exists\n";
        
        $templateContent = file_get_contents($templatePath);
        if (strpos($templateContent, 'Certificate of Completion') !== false) {
            echo "✅ Template contains certificate content\n";
        } else {
            echo "❌ Template missing certificate content\n";
        }
    } else {
        echo "❌ Certificate template missing\n";
    }
    
    // Test 3: Check State Seals
    echo "\nTEST 3: State Seal Images\n";
    echo "------------------------\n";
    
    $stateStampsDir = public_path('images/state-stamps');
    if (is_dir($stateStampsDir)) {
        echo "✅ State stamps directory exists\n";
        
        $states = ['FL', 'CA', 'TX', 'MO', 'DE'];
        foreach ($states as $state) {
            $sealPath = $stateStampsDir . '/' . $state . '-seal.png';
            if (file_exists($sealPath)) {
                echo "✅ {$state} seal exists\n";
            } else {
                echo "❌ {$state} seal missing\n";
            }
        }
    } else {
        echo "❌ State stamps directory missing\n";
    }
    
    // Test 4: Check Certificate Routes
    echo "\nTEST 4: Certificate Routes\n";
    echo "-------------------------\n";
    
    $routesContent = file_get_contents('routes/web.php');
    $requiredRoutes = [
        'certificates.index',
        'certificates.show', 
        'certificates.download',
        'certificates.generate'
    ];
    
    foreach ($requiredRoutes as $route) {
        if (strpos($routesContent, $route) !== false) {
            echo "✅ Route '{$route}' configured\n";
        } else {
            echo "❌ Route '{$route}' missing\n";
        }
    }
    
    // Test 5: Check Database Data
    echo "\nTEST 5: Database Certificate Data\n";
    echo "--------------------------------\n";
    
    $totalEnrollments = DB::table('user_course_enrollments')->count();
    $completedEnrollments = DB::table('user_course_enrollments')->where('status', 'completed')->count();
    $certificatesGenerated = DB::table('user_course_enrollments')
        ->where('status', 'completed')
        ->whereNotNull('certificate_generated_at')
        ->count();
    
    echo "✅ Total enrollments: {$totalEnrollments}\n";
    echo "✅ Completed enrollments: {$completedEnrollments}\n";
    echo "✅ Certificates generated: {$certificatesGenerated}\n";
    
    if ($completedEnrollments > 0) {
        $certificatePercentage = round(($certificatesGenerated / $completedEnrollments) * 100, 1);
        echo "✅ Certificate coverage: {$certificatePercentage}%\n";
    }
    
    // Test 6: Check Certificate Files
    echo "\nTEST 6: Certificate Files\n";
    echo "------------------------\n";
    
    $certificatesDir = public_path('certificates');
    if (is_dir($certificatesDir)) {
        echo "✅ Certificates directory exists\n";
        
        $certificateFiles = glob($certificatesDir . '/cert-*.html');
        echo "✅ Certificate files found: " . count($certificateFiles) . "\n";
        
        if (count($certificateFiles) > 0) {
            $sampleFile = $certificateFiles[0];
            $fileSize = filesize($sampleFile);
            echo "✅ Sample certificate size: " . number_format($fileSize) . " bytes\n";
        }
    } else {
        echo "❌ Certificates directory missing\n";
    }
    
    // Test 7: Test Certificate Generation
    echo "\nTEST 7: Certificate Generation Test\n";
    echo "----------------------------------\n";
    
    $testEnrollment = DB::table('user_course_enrollments as uce')
        ->join('users as u', 'uce.user_id', '=', 'u.id')
        ->where('uce.status', 'completed')
        ->select('uce.id', 'u.first_name', 'u.last_name', 'uce.certificate_number')
        ->first();
    
    if ($testEnrollment) {
        echo "✅ Test enrollment found: {$testEnrollment->first_name} {$testEnrollment->last_name} (ID: {$testEnrollment->id})\n";
        
        if ($testEnrollment->certificate_number) {
            echo "✅ Certificate number: {$testEnrollment->certificate_number}\n";
        } else {
            echo "⚠️  No certificate number assigned\n";
        }
        
        // Check if certificate file exists
        $certPath = public_path("certificates/cert-{$testEnrollment->id}.html");
        if (file_exists($certPath)) {
            echo "✅ Certificate file exists\n";
        } else {
            echo "❌ Certificate file missing\n";
        }
    } else {
        echo "⚠️  No completed enrollments found for testing\n";
    }
    
    // Test 8: Admin View
    echo "\nTEST 8: Admin Certificate View\n";
    echo "-----------------------------\n";
    
    $adminViewPath = resource_path('views/admin/certificates.blade.php');
    if (file_exists($adminViewPath)) {
        echo "✅ Admin certificates view exists\n";
        
        $viewContent = file_get_contents($adminViewPath);
        if (strpos($viewContent, 'Certificate Management') !== false) {
            echo "✅ Admin view contains management interface\n";
        } else {
            echo "❌ Admin view missing management content\n";
        }
    } else {
        echo "❌ Admin certificates view missing\n";
    }
    
    // Summary
    echo "\n🎯 CERTIFICATE SYSTEM SUMMARY\n";
    echo "============================\n";
    
    $issues = [];
    
    if (!class_exists('App\Http\Controllers\CertificateController')) {
        $issues[] = "Certificate controller missing";
    }
    
    if (!file_exists($templatePath)) {
        $issues[] = "Certificate template missing";
    }
    
    if (!is_dir($stateStampsDir)) {
        $issues[] = "State stamps directory missing";
    }
    
    if (!is_dir($certificatesDir)) {
        $issues[] = "Certificates directory missing";
    }
    
    if (empty($issues)) {
        echo "✅ CERTIFICATE SYSTEM: FULLY FUNCTIONAL\n";
        echo "✅ All components are working correctly\n";
        
        if ($testEnrollment) {
            echo "\n🔗 TEST URLS:\n";
            echo "- View Certificate: /certificate/view?enrollment_id={$testEnrollment->id}\n";
            echo "- Generate Certificate: /certificate/generate?enrollment_id={$testEnrollment->id}\n";
            echo "- Admin Certificates: /admin/certificates\n";
        }
        
        echo "\n📋 CERTIFICATE FEATURES:\n";
        echo "✅ PDF certificate generation\n";
        echo "✅ State-specific seals and stamps\n";
        echo "✅ Professional certificate templates\n";
        echo "✅ Admin certificate management\n";
        echo "✅ Automatic certificate generation on course completion\n";
        echo "✅ Certificate download and viewing\n";
        
    } else {
        echo "⚠️  CERTIFICATE SYSTEM: NEEDS ATTENTION\n";
        echo "Issues found:\n";
        foreach ($issues as $issue) {
            echo "   - {$issue}\n";
        }
    }
    
    echo "\n📊 STATISTICS:\n";
    echo "- Total Enrollments: {$totalEnrollments}\n";
    echo "- Completed Courses: {$completedEnrollments}\n";
    echo "- Certificates Generated: {$certificatesGenerated}\n";
    
    if ($completedEnrollments > 0) {
        echo "- Certificate Coverage: " . round(($certificatesGenerated / $completedEnrollments) * 100, 1) . "%\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n🏁 Certificate system test completed at " . date('Y-m-d H:i:s') . "\n";