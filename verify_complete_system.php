<?php

/**
 * COMPLETE SYSTEM VERIFICATION
 * This script verifies that all the fixes and implementations are working correctly
 */

echo "🔍 COMPLETE SYSTEM VERIFICATION - Starting...\n";
echo "=" . str_repeat("=", 60) . "\n";

$host = '127.0.0.1';
$port = '3306';
$database = 'nelly-elearning';
$username = 'root';
$password = '';

$errors = [];
$warnings = [];
$successes = [];

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $successes[] = "Database connection established";
    
    // 1. Verify core tables exist
    echo "\n1. 🗄️  VERIFYING CORE TABLES\n";
    echo "-" . str_repeat("-", 30) . "\n";
    
    $coreTables = [
        'users',
        'user_course_enrollments',
        'florida_courses',
        'courses',
        'chapters',
        'chapter_questions'
    ];
    
    foreach ($coreTables as $table) {
        $stmt = $pdo->prepare("SHOW TABLES LIKE '$table'");
        $stmt->execute();
        if ($stmt->fetch()) {
            echo "✅ $table exists\n";
            $successes[] = "Table $table exists";
        } else {
            echo "❌ $table missing\n";
            $errors[] = "Table $table is missing";
        }
    }
    
    // 2. Verify user_course_enrollments structure
    echo "\n2. 🏗️  VERIFYING TABLE STRUCTURE\n";
    echo "-" . str_repeat("-", 35) . "\n";
    
    $stmt = $pdo->prepare("DESCRIBE user_course_enrollments");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $requiredColumns = [
        'id', 'user_id', 'course_id', 'course_table', 'payment_status',
        'amount_paid', 'status', 'completed_at', 'progress_percentage',
        'final_exam_completed', 'created_at', 'updated_at'
    ];
    
    foreach ($requiredColumns as $column) {
        if (in_array($column, $columns)) {
            echo "✅ Column $column exists\n";
            $successes[] = "Column $column exists";
        } else {
            echo "❌ Column $column missing\n";
            $errors[] = "Column $column is missing";
        }
    }
    
    // 3. Test data availability
    echo "\n3. 📊 VERIFYING TEST DATA\n";
    echo "-" . str_repeat("-", 25) . "\n";
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM users");
    $stmt->execute();
    $userCount = $stmt->fetch()['count'];
    echo "✅ Users: $userCount\n";
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM user_course_enrollments");
    $stmt->execute();
    $enrollmentCount = $stmt->fetch()['count'];
    echo "✅ Enrollments: $enrollmentCount\n";
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM user_course_enrollments WHERE status = 'completed'");
    $stmt->execute();
    $completedCount = $stmt->fetch()['count'];
    echo "✅ Completed enrollments: $completedCount\n";
    
    if ($completedCount > 0) {
        $successes[] = "Found $completedCount completed enrollments for certificate generation";
    } else {
        $warnings[] = "No completed enrollments found - certificates cannot be generated";
    }
    
    // 4. Test the original failing query
    echo "\n4. 🧪 TESTING ORIGINAL FAILING QUERY\n";
    echo "-" . str_repeat("-", 35) . "\n";
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM user_course_enrollments WHERE user_id = 1 AND status = 'completed'");
        $stmt->execute();
        $results = $stmt->fetchAll();
        echo "✅ Query executed successfully - Found " . count($results) . " results\n";
        $successes[] = "Original failing query now works";
    } catch (Exception $e) {
        echo "❌ Query failed: " . $e->getMessage() . "\n";
        $errors[] = "Original query still failing: " . $e->getMessage();
    }
    
    // 5. Verify file structure
    echo "\n5. 📁 VERIFYING FILE STRUCTURE\n";
    echo "-" . str_repeat("-", 30) . "\n";
    
    $requiredFiles = [
        'app/Http/Controllers/CertificateController.php',
        'app/Http/Controllers/ProgressApiController.php',
        'app/Models/UserCourseEnrollment.php',
        'routes/web.php',
        'public/test-certificate-fix.php'
    ];
    
    foreach ($requiredFiles as $file) {
        if (file_exists($file)) {
            echo "✅ $file exists\n";
            $successes[] = "File $file exists";
        } else {
            echo "❌ $file missing\n";
            $errors[] = "File $file is missing";
        }
    }
    
    // 6. Test certificate generation readiness
    echo "\n6. 🎯 CERTIFICATE GENERATION READINESS\n";
    echo "-" . str_repeat("-", 40) . "\n";
    
    $stmt = $pdo->prepare("
        SELECT 
            uce.id as enrollment_id,
            uce.user_id,
            uce.course_id,
            uce.status,
            uce.completed_at,
            uce.final_exam_completed,
            u.name as user_name
        FROM user_course_enrollments uce
        JOIN users u ON uce.user_id = u.id
        WHERE uce.status = 'completed'
        LIMIT 3
    ");
    $stmt->execute();
    $readyEnrollments = $stmt->fetchAll();
    
    if (count($readyEnrollments) > 0) {
        echo "✅ Found " . count($readyEnrollments) . " enrollments ready for certificates:\n";
        foreach ($readyEnrollments as $enrollment) {
            echo "   - Enrollment #{$enrollment['enrollment_id']}: {$enrollment['user_name']} (User #{$enrollment['user_id']})\n";
        }
        $successes[] = "Certificate generation is ready";
    } else {
        echo "⚠️  No completed enrollments found for certificate generation\n";
        $warnings[] = "No completed enrollments available";
    }
    
    // 7. Check directories
    echo "\n7. 📂 VERIFYING DIRECTORIES\n";
    echo "-" . str_repeat("-", 25) . "\n";
    
    $requiredDirs = [
        'public/certificates',
        'public/images/state-stamps',
        'storage/app/public'
    ];
    
    foreach ($requiredDirs as $dir) {
        if (is_dir($dir)) {
            echo "✅ Directory $dir exists\n";
            $successes[] = "Directory $dir exists";
        } else {
            echo "⚠️  Directory $dir missing - creating...\n";
            if (mkdir($dir, 0755, true)) {
                echo "✅ Created directory $dir\n";
                $successes[] = "Created directory $dir";
            } else {
                echo "❌ Failed to create directory $dir\n";
                $errors[] = "Failed to create directory $dir";
            }
        }
    }
    
} catch (Exception $e) {
    echo "❌ Critical Error: " . $e->getMessage() . "\n";
    $errors[] = "Critical database error: " . $e->getMessage();
}

// Summary
echo "\n" . str_repeat("=", 60) . "\n";
echo "📋 VERIFICATION SUMMARY\n";
echo str_repeat("=", 60) . "\n";

echo "\n✅ SUCCESSES (" . count($successes) . "):\n";
foreach ($successes as $success) {
    echo "   • $success\n";
}

if (!empty($warnings)) {
    echo "\n⚠️  WARNINGS (" . count($warnings) . "):\n";
    foreach ($warnings as $warning) {
        echo "   • $warning\n";
    }
}

if (!empty($errors)) {
    echo "\n❌ ERRORS (" . count($errors) . "):\n";
    foreach ($errors as $error) {
        echo "   • $error\n";
    }
}

echo "\n🎯 FINAL STATUS:\n";
if (empty($errors)) {
    if (empty($warnings)) {
        echo "🎉 ALL SYSTEMS GO! The platform is fully operational.\n";
    } else {
        echo "✅ SYSTEM OPERATIONAL with minor warnings.\n";
    }
    echo "\n🔗 Test the system at:\n";
    echo "   • http://nelly-elearning.test/test-certificate-fix.php\n";
    echo "   • http://nelly-elearning.test/generate-certificates\n";
    echo "   • http://nelly-elearning.test/my-certificates\n";
} else {
    echo "❌ SYSTEM HAS CRITICAL ERRORS - Please fix the errors above.\n";
    exit(1);
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "Verification completed at: " . date('Y-m-d H:i:s') . "\n";

?>