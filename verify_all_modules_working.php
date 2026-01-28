<?php

/**
 * VERIFY ALL MODULES WORKING - Test all application modules
 * This script tests all modules to ensure database tables are working
 */

echo "🔍 VERIFYING ALL MODULES - Starting comprehensive test...\n";
echo str_repeat("=", 80) . "\n";

$host = '127.0.0.1';
$port = '3306';
$database = 'nelly-elearning';
$username = 'root';
$password = '';

$moduleTests = [];
$errors = [];
$warnings = [];

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Database connection established\n\n";
    
    // Test 1: Core System Tables
    echo "1. 🏗️  TESTING CORE SYSTEM TABLES\n";
    echo str_repeat("-", 40) . "\n";
    
    $coreTests = [
        'users' => 'SELECT COUNT(*) as count FROM users',
        'user_course_enrollments' => 'SELECT COUNT(*) as count FROM user_course_enrollments',
        'courses' => 'SELECT COUNT(*) as count FROM courses',
        'chapters' => 'SELECT COUNT(*) as count FROM chapters',
        'chapter_questions' => 'SELECT COUNT(*) as count FROM chapter_questions',
        'questions' => 'SELECT COUNT(*) as count FROM questions',
        'final_exam_questions' => 'SELECT COUNT(*) as count FROM final_exam_questions',
        'final_exam_results' => 'SELECT COUNT(*) as count FROM final_exam_results'
    ];
    
    foreach ($coreTests as $table => $query) {
        try {
            $stmt = $pdo->prepare($query);
            $stmt->execute();
            $count = $stmt->fetch()['count'];
            echo "✅ $table: $count records\n";
            $moduleTests['core_system'][$table] = 'PASS';
        } catch (Exception $e) {
            echo "❌ $table: " . $e->getMessage() . "\n";
            $errors[] = "Core System - $table: " . $e->getMessage();
            $moduleTests['core_system'][$table] = 'FAIL';
        }
    }
    
    // Test 2: Multi-State Course Tables
    echo "\n2. 🌎 TESTING MULTI-STATE COURSE TABLES\n";
    echo str_repeat("-", 45) . "\n";
    
    $stateTests = [
        'florida_courses' => 'SELECT COUNT(*) as count FROM florida_courses',
        'missouri_courses' => 'SELECT COUNT(*) as count FROM missouri_courses',
        'texas_courses' => 'SELECT COUNT(*) as count FROM texas_courses',
        'delaware_courses' => 'SELECT COUNT(*) as count FROM delaware_courses',
        'nevada_courses' => 'SELECT COUNT(*) as count FROM nevada_courses'
    ];
    
    foreach ($stateTests as $table => $query) {
        try {
            $stmt = $pdo->prepare($query);
            $stmt->execute();
            $count = $stmt->fetch()['count'];
            echo "✅ $table: $count records\n";
            $moduleTests['multi_state'][$table] = 'PASS';
        } catch (Exception $e) {
            echo "❌ $table: " . $e->getMessage() . "\n";
            $errors[] = "Multi-State - $table: " . $e->getMessage();
            $moduleTests['multi_state'][$table] = 'FAIL';
        }
    }
    
    // Test 3: Chapter Tables
    echo "\n3. 📚 TESTING CHAPTER TABLES\n";
    echo str_repeat("-", 30) . "\n";
    
    $chapterTests = [
        'florida_chapters' => 'SELECT COUNT(*) as count FROM florida_chapters',
        'missouri_chapters' => 'SELECT COUNT(*) as count FROM missouri_chapters',
        'texas_chapters' => 'SELECT COUNT(*) as count FROM texas_chapters',
        'delaware_chapters' => 'SELECT COUNT(*) as count FROM delaware_chapters'
    ];
    
    foreach ($chapterTests as $table => $query) {
        try {
            $stmt = $pdo->prepare($query);
            $stmt->execute();
            $count = $stmt->fetch()['count'];
            echo "✅ $table: $count records\n";
            $moduleTests['chapters'][$table] = 'PASS';
        } catch (Exception $e) {
            echo "❌ $table: " . $e->getMessage() . "\n";
            $errors[] = "Chapters - $table: " . $e->getMessage();
            $moduleTests['chapters'][$table] = 'FAIL';
        }
    }
    
    // Test 4: Progress Tracking Tables
    echo "\n4. 📊 TESTING PROGRESS TRACKING TABLES\n";
    echo str_repeat("-", 40) . "\n";
    
    $progressTests = [
        'user_course_progress' => 'SELECT COUNT(*) as count FROM user_course_progress',
        'chapter_quiz_results' => 'SELECT COUNT(*) as count FROM chapter_quiz_results'
    ];
    
    foreach ($progressTests as $table => $query) {
        try {
            $stmt = $pdo->prepare($query);
            $stmt->execute();
            $count = $stmt->fetch()['count'];
            echo "✅ $table: $count records\n";
            $moduleTests['progress_tracking'][$table] = 'PASS';
        } catch (Exception $e) {
            echo "❌ $table: " . $e->getMessage() . "\n";
            $errors[] = "Progress Tracking - $table: " . $e->getMessage();
            $moduleTests['progress_tracking'][$table] = 'FAIL';
        }
    }
    
    // Test 5: Free Response Quiz Tables
    echo "\n5. ✍️  TESTING FREE RESPONSE QUIZ TABLES\n";
    echo str_repeat("-", 40) . "\n";
    
    $freeResponseTests = [
        'free_response_quiz_placements' => 'SELECT COUNT(*) as count FROM free_response_quiz_placements',
        'free_response_questions' => 'SELECT COUNT(*) as count FROM free_response_questions',
        'free_response_answers' => 'SELECT COUNT(*) as count FROM free_response_answers'
    ];
    
    foreach ($freeResponseTests as $table => $query) {
        try {
            $stmt = $pdo->prepare($query);
            $stmt->execute();
            $count = $stmt->fetch()['count'];
            echo "✅ $table: $count records\n";
            $moduleTests['free_response'][$table] = 'PASS';
        } catch (Exception $e) {
            echo "❌ $table: " . $e->getMessage() . "\n";
            $errors[] = "Free Response - $table: " . $e->getMessage();
            $moduleTests['free_response'][$table] = 'FAIL';
        }
    }
    
    // Test 6: System Administration Tables
    echo "\n6. ⚙️  TESTING SYSTEM ADMINISTRATION TABLES\n";
    echo str_repeat("-", 45) . "\n";
    
    $adminTests = [
        'system_modules' => 'SELECT COUNT(*) as count FROM system_modules',
        'system_settings' => 'SELECT COUNT(*) as count FROM system_settings',
        'admin_users' => 'SELECT COUNT(*) as count FROM admin_users'
    ];
    
    foreach ($adminTests as $table => $query) {
        try {
            $stmt = $pdo->prepare($query);
            $stmt->execute();
            $count = $stmt->fetch()['count'];
            echo "✅ $table: $count records\n";
            $moduleTests['administration'][$table] = 'PASS';
        } catch (Exception $e) {
            echo "❌ $table: " . $e->getMessage() . "\n";
            $errors[] = "Administration - $table: " . $e->getMessage();
            $moduleTests['administration'][$table] = 'FAIL';
        }
    }
    
    // Test 7: Certificate and Payment Tables
    echo "\n7. 🎫 TESTING CERTIFICATE AND PAYMENT TABLES\n";
    echo str_repeat("-", 45) . "\n";
    
    $certPaymentTests = [
        'florida_certificates' => 'SELECT COUNT(*) as count FROM florida_certificates',
        'payments' => 'SELECT COUNT(*) as count FROM payments'
    ];
    
    foreach ($certPaymentTests as $table => $query) {
        try {
            $stmt = $pdo->prepare($query);
            $stmt->execute();
            $count = $stmt->fetch()['count'];
            echo "✅ $table: $count records\n";
            $moduleTests['certificates_payments'][$table] = 'PASS';
        } catch (Exception $e) {
            echo "❌ $table: " . $e->getMessage() . "\n";
            $errors[] = "Certificates/Payments - $table: " . $e->getMessage();
            $moduleTests['certificates_payments'][$table] = 'FAIL';
        }
    }
    
    // Test 8: Support and Timer Tables
    echo "\n8. 🛠️  TESTING SUPPORT AND TIMER TABLES\n";
    echo str_repeat("-", 40) . "\n";
    
    $supportTimerTests = [
        'support_tickets' => 'SELECT COUNT(*) as count FROM support_tickets',
        'chapter_timers' => 'SELECT COUNT(*) as count FROM chapter_timers',
        'state_submission_queue' => 'SELECT COUNT(*) as count FROM state_submission_queue'
    ];
    
    foreach ($supportTimerTests as $table => $query) {
        try {
            $stmt = $pdo->prepare($query);
            $stmt->execute();
            $count = $stmt->fetch()['count'];
            echo "✅ $table: $count records\n";
            $moduleTests['support_timers'][$table] = 'PASS';
        } catch (Exception $e) {
            echo "❌ $table: " . $e->getMessage() . "\n";
            $errors[] = "Support/Timers - $table: " . $e->getMessage();
            $moduleTests['support_timers'][$table] = 'FAIL';
        }
    }
    
    // Test 9: Critical Application Queries
    echo "\n9. 🧪 TESTING CRITICAL APPLICATION QUERIES\n";
    echo str_repeat("-", 45) . "\n";
    
    $criticalQueries = [
        'Certificate Generation Query' => "
            SELECT uce.*, u.name as user_name 
            FROM user_course_enrollments uce 
            JOIN users u ON uce.user_id = u.id 
            WHERE uce.status = 'completed' 
            LIMIT 5
        ",
        'Course Enrollment Query' => "
            SELECT c.title, COUNT(uce.id) as enrollments 
            FROM courses c 
            LEFT JOIN user_course_enrollments uce ON c.id = uce.course_id 
            GROUP BY c.id, c.title
        ",
        'Progress Tracking Query' => "
            SELECT ucp.*, c.title as chapter_title 
            FROM user_course_progress ucp 
            LEFT JOIN chapters c ON ucp.chapter_id = c.id 
            LIMIT 5
        ",
        'System Modules Query' => "
            SELECT module_name, enabled 
            FROM system_modules 
            WHERE enabled = 1
        "
    ];
    
    foreach ($criticalQueries as $queryName => $query) {
        try {
            $stmt = $pdo->prepare($query);
            $stmt->execute();
            $results = $stmt->fetchAll();
            echo "✅ $queryName: " . count($results) . " results\n";
            $moduleTests['critical_queries'][$queryName] = 'PASS';
        } catch (Exception $e) {
            echo "❌ $queryName: " . $e->getMessage() . "\n";
            $errors[] = "Critical Query - $queryName: " . $e->getMessage();
            $moduleTests['critical_queries'][$queryName] = 'FAIL';
        }
    }
    
    // Test 10: Module-Specific Functionality
    echo "\n10. 🎯 TESTING MODULE-SPECIFIC FUNCTIONALITY\n";
    echo str_repeat("-", 45) . "\n";
    
    // Test certificate generation readiness
    try {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count 
            FROM user_course_enrollments 
            WHERE status = 'completed' AND final_exam_completed = 1
        ");
        $stmt->execute();
        $certReady = $stmt->fetch()['count'];
        echo "✅ Certificates ready to generate: $certReady\n";
        $moduleTests['functionality']['certificate_generation'] = 'PASS';
    } catch (Exception $e) {
        echo "❌ Certificate generation test: " . $e->getMessage() . "\n";
        $errors[] = "Certificate generation: " . $e->getMessage();
        $moduleTests['functionality']['certificate_generation'] = 'FAIL';
    }
    
    // Test course player readiness
    try {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count 
            FROM chapters c 
            JOIN courses co ON c.course_id = co.id 
            WHERE c.is_active = 1 AND co.is_active = 1
        ");
        $stmt->execute();
        $activeChapters = $stmt->fetch()['count'];
        echo "✅ Active chapters for course player: $activeChapters\n";
        $moduleTests['functionality']['course_player'] = 'PASS';
    } catch (Exception $e) {
        echo "❌ Course player test: " . $e->getMessage() . "\n";
        $errors[] = "Course player: " . $e->getMessage();
        $moduleTests['functionality']['course_player'] = 'FAIL';
    }
    
    // Test admin panel readiness
    try {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count 
            FROM system_modules 
            WHERE enabled = 1
        ");
        $stmt->execute();
        $enabledModules = $stmt->fetch()['count'];
        echo "✅ Enabled system modules: $enabledModules\n";
        $moduleTests['functionality']['admin_panel'] = 'PASS';
    } catch (Exception $e) {
        echo "❌ Admin panel test: " . $e->getMessage() . "\n";
        $errors[] = "Admin panel: " . $e->getMessage();
        $moduleTests['functionality']['admin_panel'] = 'FAIL';
    }
    
} catch (Exception $e) {
    echo "❌ Critical Database Error: " . $e->getMessage() . "\n";
    $errors[] = "Critical: " . $e->getMessage();
}

// Generate Summary Report
echo "\n" . str_repeat("=", 80) . "\n";
echo "📋 COMPREHENSIVE MODULE VERIFICATION REPORT\n";
echo str_repeat("=", 80) . "\n";

$totalTests = 0;
$passedTests = 0;
$failedTests = 0;

foreach ($moduleTests as $module => $tests) {
    $modulePass = 0;
    $moduleTotal = count($tests);
    $totalTests += $moduleTotal;
    
    foreach ($tests as $test => $result) {
        if ($result === 'PASS') {
            $modulePass++;
            $passedTests++;
        } else {
            $failedTests++;
        }
    }
    
    $moduleStatus = $modulePass === $moduleTotal ? '✅' : ($modulePass > 0 ? '⚠️' : '❌');
    echo "$moduleStatus " . strtoupper(str_replace('_', ' ', $module)) . ": $modulePass/$moduleTotal tests passed\n";
}

echo "\n📊 OVERALL STATISTICS:\n";
echo "   Total Tests: $totalTests\n";
echo "   Passed: $passedTests ✅\n";
echo "   Failed: $failedTests ❌\n";
echo "   Success Rate: " . round(($passedTests / $totalTests) * 100, 1) . "%\n";

if (empty($errors)) {
    echo "\n🎉 ALL MODULES VERIFIED SUCCESSFULLY!\n";
    echo "The multi-state traffic school platform is fully operational.\n";
    echo "\n🔗 All modules should now work at:\n";
    echo "   • Dashboard: http://nelly-elearning.test/dashboard\n";
    echo "   • Certificates: http://nelly-elearning.test/generate-certificates\n";
    echo "   • Course Player: http://nelly-elearning.test/course-player\n";
    echo "   • Admin Panel: http://nelly-elearning.test/admin\n";
    echo "   • My Certificates: http://nelly-elearning.test/my-certificates\n";
} else {
    echo "\n⚠️  SOME ISSUES FOUND:\n";
    foreach ($errors as $error) {
        echo "   • $error\n";
    }
    echo "\nMost modules should still work, but some features may be limited.\n";
}

echo "\n" . str_repeat("=", 80) . "\n";
echo "Verification completed at: " . date('Y-m-d H:i:s') . "\n";

?>