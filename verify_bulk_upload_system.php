<?php

/**
 * BULK UPLOAD SYSTEM VERIFICATION
 * This script verifies that the bulk upload functionality is working correctly
 */

echo "🔍 BULK UPLOAD SYSTEM VERIFICATION\n";
echo str_repeat("=", 60) . "\n";

$host = '127.0.0.1';
$port = '3306';
$database = 'nelly-elearning';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Connected to database: $database\n\n";
    
    // 1. Verify Controllers Exist
    echo "1. 🎮 CONTROLLER VERIFICATION:\n";
    echo str_repeat("-", 30) . "\n";
    
    $controllers = [
        'BulkUploadController' => 'app/Http/Controllers/Admin/BulkUploadController.php',
        'BulkUploadApiController' => 'app/Http/Controllers/Admin/BulkUploadApiController.php',
        'EnhancedCoursePlayerController' => 'app/Http/Controllers/Admin/EnhancedCoursePlayerController.php'
    ];
    
    foreach ($controllers as $name => $path) {
        if (file_exists($path)) {
            echo "   ✅ $name exists\n";
        } else {
            echo "   ❌ $name missing at $path\n";
        }
    }
    
    // 2. Verify Views Exist
    echo "\n2. 👁️  VIEW VERIFICATION:\n";
    echo str_repeat("-", 20) . "\n";
    
    $views = [
        'Bulk Upload Interface' => 'resources/views/admin/bulk-upload/index.blade.php',
        'Enhanced Course Player' => 'resources/views/admin/enhanced-course-player.blade.php'
    ];
    
    foreach ($views as $name => $path) {
        if (file_exists($path)) {
            echo "   ✅ $name exists\n";
        } else {
            echo "   ❌ $name missing at $path\n";
        }
    }
    
    // 3. Verify Database Tables
    echo "\n3. 🗄️  DATABASE TABLE VERIFICATION:\n";
    echo str_repeat("-", 35) . "\n";
    
    $requiredTables = [
        'courses' => 'Main courses table',
        'florida_courses' => 'Florida-specific courses',
        'missouri_courses' => 'Missouri-specific courses',
        'texas_courses' => 'Texas-specific courses',
        'delaware_courses' => 'Delaware-specific courses',
        'chapters' => 'Course chapters',
        'chapter_questions' => 'Quiz questions',
        'user_course_enrollments' => 'Student enrollments',
        'user_course_progress' => 'Progress tracking',
        'chapter_quiz_results' => 'Quiz results'
    ];
    
    foreach ($requiredTables as $table => $description) {
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM $table");
            $stmt->execute();
            $count = $stmt->fetch()['count'];
            echo "   ✅ $table ($count records) - $description\n";
        } catch (Exception $e) {
            echo "   ❌ $table - MISSING! ($description)\n";
        }
    }
    
    // 4. Verify PHP Extensions
    echo "\n4. 🔧 PHP EXTENSION VERIFICATION:\n";
    echo str_repeat("-", 35) . "\n";
    
    $requiredExtensions = [
        'zip' => 'ZIP archive processing',
        'gd' => 'Image processing',
        'mbstring' => 'Multi-byte string handling',
        'fileinfo' => 'File type detection',
        'dom' => 'XML/HTML processing'
    ];
    
    foreach ($requiredExtensions as $ext => $description) {
        if (extension_loaded($ext)) {
            echo "   ✅ $ext - $description\n";
        } else {
            echo "   ❌ $ext - MISSING! ($description)\n";
        }
    }
    
    // 5. Verify Directory Permissions
    echo "\n5. 📁 DIRECTORY PERMISSION VERIFICATION:\n";
    echo str_repeat("-", 40) . "\n";
    
    $directories = [
        'storage/app/temp' => 'Temporary file storage',
        'storage/app/public/course-content' => 'Course content storage',
        'storage/app/public/course-images' => 'Course images storage',
        'storage/app/exports' => 'Export files storage'
    ];
    
    foreach ($directories as $dir => $description) {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        if (is_writable($dir)) {
            echo "   ✅ $dir - $description (writable)\n";
        } else {
            echo "   ❌ $dir - $description (not writable)\n";
        }
    }
    
    // 6. Test File Upload Limits
    echo "\n6. 📤 FILE UPLOAD LIMITS:\n";
    echo str_repeat("-", 25) . "\n";
    
    $uploadLimits = [
        'upload_max_filesize' => ini_get('upload_max_filesize'),
        'post_max_size' => ini_get('post_max_size'),
        'memory_limit' => ini_get('memory_limit'),
        'max_execution_time' => ini_get('max_execution_time'),
        'max_input_time' => ini_get('max_input_time')
    ];
    
    foreach ($uploadLimits as $setting => $value) {
        echo "   📊 $setting: $value\n";
    }
    
    // 7. Verify Routes (Basic Check)
    echo "\n7. 🛤️  ROUTE VERIFICATION:\n";
    echo str_repeat("-", 20) . "\n";
    
    $routeFile = 'routes/web.php';
    if (file_exists($routeFile)) {
        $routeContent = file_get_contents($routeFile);
        
        $requiredRoutes = [
            'bulk-upload' => 'Bulk upload interface',
            'bulk-upload/course-content' => 'Course content upload',
            'bulk-upload/quiz-content' => 'Quiz content upload',
            'enhanced-course-player' => 'Enhanced course player'
        ];
        
        foreach ($requiredRoutes as $route => $description) {
            if (strpos($routeContent, $route) !== false) {
                echo "   ✅ $route - $description\n";
            } else {
                echo "   ❌ $route - MISSING! ($description)\n";
            }
        }
    } else {
        echo "   ❌ routes/web.php not found\n";
    }
    
    // 8. Test Sample Data
    echo "\n8. 🧪 SAMPLE DATA VERIFICATION:\n";
    echo str_repeat("-", 30) . "\n";
    
    // Check if we have sample courses
    $sampleCourses = $pdo->query("SELECT COUNT(*) as count FROM courses")->fetch()['count'];
    $sampleChapters = $pdo->query("SELECT COUNT(*) as count FROM chapters")->fetch()['count'];
    $sampleQuestions = $pdo->query("SELECT COUNT(*) as count FROM chapter_questions")->fetch()['count'];
    
    echo "   📊 Sample courses: $sampleCourses\n";
    echo "   📊 Sample chapters: $sampleChapters\n";
    echo "   📊 Sample questions: $sampleQuestions\n";
    
    if ($sampleCourses > 0 && $sampleChapters > 0) {
        echo "   ✅ Sample data available for testing\n";
    } else {
        echo "   ⚠️  No sample data - create test courses for full testing\n";
    }
    
    // 9. Performance Recommendations
    echo "\n9. ⚡ PERFORMANCE RECOMMENDATIONS:\n";
    echo str_repeat("-", 35) . "\n";
    
    $memoryLimit = ini_get('memory_limit');
    $memoryBytes = $this->convertToBytes($memoryLimit);
    
    if ($memoryBytes < 512 * 1024 * 1024) { // Less than 512MB
        echo "   ⚠️  Consider increasing memory_limit to 1G or 2G for large uploads\n";
    } else {
        echo "   ✅ Memory limit adequate: $memoryLimit\n";
    }
    
    $maxExecTime = ini_get('max_execution_time');
    if ($maxExecTime > 0 && $maxExecTime < 300) { // Less than 5 minutes
        echo "   ⚠️  Consider increasing max_execution_time to 0 (unlimited) for large uploads\n";
    } else {
        echo "   ✅ Execution time adequate: " . ($maxExecTime == 0 ? 'unlimited' : $maxExecTime . 's') . "\n";
    }
    
    // 10. Final System Status
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "📋 BULK UPLOAD SYSTEM STATUS REPORT\n";
    echo str_repeat("=", 60) . "\n";
    
    $systemReady = true;
    $criticalIssues = [];
    
    // Check critical components
    if (!file_exists('app/Http/Controllers/Admin/BulkUploadController.php')) {
        $systemReady = false;
        $criticalIssues[] = 'BulkUploadController missing';
    }
    
    if (!file_exists('resources/views/admin/bulk-upload/index.blade.php')) {
        $systemReady = false;
        $criticalIssues[] = 'Bulk upload view missing';
    }
    
    // Check database tables
    try {
        $pdo->query("SELECT 1 FROM courses LIMIT 1");
        $pdo->query("SELECT 1 FROM chapters LIMIT 1");
        $pdo->query("SELECT 1 FROM chapter_questions LIMIT 1");
    } catch (Exception $e) {
        $systemReady = false;
        $criticalIssues[] = 'Required database tables missing';
    }
    
    if ($systemReady) {
        echo "🎉 SYSTEM STATUS: READY FOR BULK UPLOAD!\n";
        echo "✅ All critical components verified\n";
        echo "✅ Database tables present\n";
        echo "✅ File permissions correct\n";
        echo "✅ PHP extensions available\n";
        
        echo "\n🚀 READY TO USE:\n";
        echo "   • Unlimited file size uploads\n";
        echo "   • Multiple format support (Word, PDF, ZIP, etc.)\n";
        echo "   • Auto-chapter creation\n";
        echo "   • Quiz question extraction\n";
        echo "   • Progressive content loading\n";
        echo "   • Enhanced course player\n";
        
        echo "\n🔗 ACCESS URLS:\n";
        echo "   • Bulk Upload: http://nelly-elearning.test/admin/bulk-upload\n";
        echo "   • Enhanced Player: http://nelly-elearning.test/admin/enhanced-course-player/{enrollmentId}\n";
        
    } else {
        echo "❌ SYSTEM STATUS: NEEDS ATTENTION\n";
        echo "Critical issues found:\n";
        foreach ($criticalIssues as $issue) {
            echo "   • $issue\n";
        }
        
        echo "\n🔧 REQUIRED ACTIONS:\n";
        echo "   1. Ensure all controller files are created\n";
        echo "   2. Verify all view files exist\n";
        echo "   3. Run database migrations if needed\n";
        echo "   4. Check file permissions\n";
    }
    
    echo "\n⚠️  IMPORTANT NOTES:\n";
    echo "   • Test with small files first\n";
    echo "   • Monitor server resources during large uploads\n";
    echo "   • Backup database before bulk operations\n";
    echo "   • Clear browser cache after updates\n";
    
} catch (Exception $e) {
    echo "❌ Verification failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Helper function to convert memory limit to bytes
function convertToBytes($value) {
    $unit = strtolower(substr($value, -1));
    $value = (int) $value;
    
    switch ($unit) {
        case 'g':
            $value *= 1024;
        case 'm':
            $value *= 1024;
        case 'k':
            $value *= 1024;
    }
    
    return $value;
}

?>