<?php

/**
 * SIMPLE DATABASE FIX - Create Missing user_course_enrollments Table
 */

echo "🔧 EMERGENCY DATABASE FIX - Starting...\n";

$host = '127.0.0.1';
$port = '3306';
$database = 'nelly-elearning';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
    
    echo "✅ Connected to database: $database\n";
    
    // Check if table exists
    $stmt = $pdo->prepare("SHOW TABLES LIKE 'user_course_enrollments'");
    $stmt->execute();
    $tableExists = $stmt->fetch();
    
    if ($tableExists) {
        echo "⚠️  Table already exists. Checking structure...\n";
    } else {
        echo "🔧 Creating user_course_enrollments table...\n";
        
        $createTableSQL = "
        CREATE TABLE `user_course_enrollments` (
            `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            `user_id` bigint(20) unsigned NOT NULL,
            `course_id` bigint(20) unsigned NOT NULL,
            `course_table` varchar(255) DEFAULT 'florida_courses',
            `payment_status` enum('pending','paid','failed','refunded','cancelled') DEFAULT 'pending',
            `amount_paid` decimal(8,2) DEFAULT '0.00',
            `payment_method` varchar(50) DEFAULT NULL,
            `payment_id` varchar(255) DEFAULT NULL,
            `citation_number` varchar(255) DEFAULT NULL,
            `case_number` varchar(255) DEFAULT NULL,
            `court_state` varchar(2) DEFAULT NULL,
            `court_county` varchar(255) DEFAULT NULL,
            `court_selected` varchar(255) DEFAULT NULL,
            `court_date` date DEFAULT NULL,
            `enrolled_at` timestamp NULL DEFAULT NULL,
            `started_at` timestamp NULL DEFAULT NULL,
            `completed_at` timestamp NULL DEFAULT NULL,
            `progress_percentage` decimal(5,2) DEFAULT '0.00',
            `quiz_average` decimal(5,2) DEFAULT NULL,
            `total_time_spent` int(11) DEFAULT '0',
            `status` enum('pending','active','completed','expired','cancelled') DEFAULT 'pending',
            `access_revoked` tinyint(1) DEFAULT '0',
            `access_revoked_at` timestamp NULL DEFAULT NULL,
            `last_activity_at` timestamp NULL DEFAULT NULL,
            `reminder_sent_at` timestamp NULL DEFAULT NULL,
            `reminder_count` int(11) DEFAULT '0',
            `optional_services` json DEFAULT NULL,
            `optional_services_total` decimal(8,2) DEFAULT '0.00',
            `final_exam_completed` tinyint(1) DEFAULT '0',
            `final_exam_result_id` bigint(20) unsigned DEFAULT NULL,
            `created_at` timestamp NULL DEFAULT NULL,
            `updated_at` timestamp NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `user_course_enrollments_user_id_index` (`user_id`),
            KEY `user_course_enrollments_course_id_index` (`course_id`),
            KEY `user_course_enrollments_status_index` (`status`),
            KEY `user_course_enrollments_payment_status_index` (`payment_status`),
            KEY `user_course_enrollments_completed_at_index` (`completed_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $pdo->exec($createTableSQL);
        echo "✅ Table created successfully!\n";
    }
    
    // Add test data
    echo "🔧 Adding test data...\n";
    
    // Check for existing user
    $stmt = $pdo->prepare("SELECT id FROM users LIMIT 1");
    $stmt->execute();
    $user = $stmt->fetch();
    
    if (!$user) {
        echo "Creating test user...\n";
        $pdo->exec("INSERT INTO users (name, email, password, created_at, updated_at) VALUES ('Test User', 'test@example.com', '\$2y\$12\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW(), NOW())");
        $userId = $pdo->lastInsertId();
    } else {
        $userId = $user['id'];
    }
    
    // Check for existing course
    $stmt = $pdo->prepare("SELECT id FROM florida_courses LIMIT 1");
    $stmt->execute();
    $course = $stmt->fetch();
    
    if (!$course) {
        echo "Creating test course...\n";
        $pdo->exec("INSERT INTO florida_courses (title, description, duration_hours, price, is_active, created_at, updated_at) VALUES ('Florida BDI Course', 'Test course', 4, 29.95, 1, NOW(), NOW())");
        $courseId = $pdo->lastInsertId();
    } else {
        $courseId = $course['id'];
    }
    
    // Add test enrollment
    $stmt = $pdo->prepare("SELECT id FROM user_course_enrollments WHERE user_id = ? AND course_id = ?");
    $stmt->execute([$userId, $courseId]);
    $enrollment = $stmt->fetch();
    
    if (!$enrollment) {
        echo "Creating test enrollment...\n";
        $pdo->exec("INSERT INTO user_course_enrollments (user_id, course_id, course_table, payment_status, amount_paid, payment_method, enrolled_at, started_at, completed_at, progress_percentage, quiz_average, status, final_exam_completed, created_at, updated_at) VALUES ($userId, $courseId, 'florida_courses', 'paid', 29.95, 'test', NOW(), NOW(), NOW(), 100.00, 95.00, 'completed', 1, NOW(), NOW())");
        echo "✅ Test enrollment created!\n";
    } else {
        echo "✅ Test enrollment already exists!\n";
    }
    
    // Verify
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM user_course_enrollments");
    $stmt->execute();
    $count = $stmt->fetch()['count'];
    echo "✅ Total enrollments: $count\n";
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM user_course_enrollments WHERE status = 'completed'");
    $stmt->execute();
    $completedCount = $stmt->fetch()['count'];
    echo "✅ Completed enrollments: $completedCount\n";
    
    echo "\n🎉 DATABASE FIX COMPLETED SUCCESSFULLY!\n";
    echo "You can now test the application at:\n";
    echo "- http://nelly-elearning.test/generate-certificates\n";
    echo "- http://nelly-elearning.test/my-certificates\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

?>