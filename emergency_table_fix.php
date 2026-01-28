<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🚨 EMERGENCY TABLE FIX\n";
echo "======================\n\n";

try {
    // Get database connection details from .env
    $host = env('DB_HOST', '127.0.0.1');
    $port = env('DB_PORT', '3306');
    $database = env('DB_DATABASE', 'nelly-elearning');
    $username = env('DB_USERNAME', 'root');
    $password = env('DB_PASSWORD', '');
    
    echo "Connecting to database: {$database} on {$host}:{$port}\n";
    
    // Create PDO connection
    $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    
    echo "✅ Database connection successful\n\n";
    
    // Drop table if exists
    echo "Dropping existing table if it exists...\n";
    $pdo->exec("DROP TABLE IF EXISTS `user_course_enrollments`");
    echo "✅ Table dropped\n";
    
    // Create table
    echo "Creating user_course_enrollments table...\n";
    $createTableSQL = "
        CREATE TABLE `user_course_enrollments` (
          `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
          `user_id` bigint(20) unsigned NOT NULL,
          `course_id` bigint(20) unsigned NOT NULL,
          `course_table` varchar(255) DEFAULT 'florida_courses',
          `payment_status` enum('pending','paid','failed','refunded') DEFAULT 'pending',
          `amount_paid` decimal(8,2) DEFAULT NULL,
          `payment_method` varchar(255) DEFAULT NULL,
          `payment_id` varchar(255) DEFAULT NULL,
          `citation_number` varchar(255) DEFAULT NULL,
          `case_number` varchar(255) DEFAULT NULL,
          `court_state` varchar(255) DEFAULT NULL,
          `court_county` varchar(255) DEFAULT NULL,
          `court_selected` varchar(255) DEFAULT NULL,
          `court_date` date DEFAULT NULL,
          `enrolled_at` timestamp NULL DEFAULT NULL,
          `started_at` timestamp NULL DEFAULT NULL,
          `completed_at` timestamp NULL DEFAULT NULL,
          `progress_percentage` decimal(5,2) DEFAULT 0.00,
          `quiz_average` decimal(5,2) DEFAULT NULL,
          `total_time_spent` int(11) DEFAULT 0,
          `status` enum('pending','active','completed','expired','cancelled') DEFAULT 'pending',
          `access_revoked` tinyint(1) DEFAULT 0,
          `access_revoked_at` timestamp NULL DEFAULT NULL,
          `last_activity_at` timestamp NULL DEFAULT NULL,
          `reminder_sent_at` timestamp NULL DEFAULT NULL,
          `reminder_count` int(11) DEFAULT 0,
          `optional_services` json DEFAULT NULL,
          `optional_services_total` decimal(8,2) DEFAULT 0.00,
          `final_exam_completed` tinyint(1) DEFAULT 0,
          `final_exam_result_id` bigint(20) unsigned DEFAULT NULL,
          `certificate_generated_at` timestamp NULL DEFAULT NULL,
          `certificate_number` varchar(255) DEFAULT NULL,
          `certificate_path` varchar(500) DEFAULT NULL,
          `created_at` timestamp NULL DEFAULT NULL,
          `updated_at` timestamp NULL DEFAULT NULL,
          PRIMARY KEY (`id`),
          KEY `user_course_enrollments_user_id_index` (`user_id`),
          KEY `user_course_enrollments_course_id_index` (`course_id`),
          KEY `user_course_enrollments_status_index` (`status`),
          KEY `user_course_enrollments_payment_status_index` (`payment_status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";
    
    $pdo->exec($createTableSQL);
    echo "✅ Table created successfully\n";
    
    // Check if users table exists and has data
    $userCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    echo "Users table has {$userCount} records\n";
    
    if ($userCount == 0) {
        echo "Creating test user...\n";
        $pdo->exec("
            INSERT INTO users (first_name, last_name, email, password, created_at, updated_at) 
            VALUES ('Test', 'User', 'test@example.com', '" . password_hash('password', PASSWORD_DEFAULT) . "', NOW(), NOW())
        ");
        echo "✅ Test user created\n";
    }
    
    // Check if florida_courses table exists
    try {
        $courseCount = $pdo->query("SELECT COUNT(*) FROM florida_courses")->fetchColumn();
        echo "Florida courses table has {$courseCount} records\n";
        
        if ($courseCount == 0) {
            echo "Creating test course...\n";
            $pdo->exec("
                INSERT INTO florida_courses (title, description, state_code, is_active, created_at, updated_at) 
                VALUES ('Florida Traffic School Course', 'Basic traffic school course for Florida', 'FL', 1, NOW(), NOW())
            ");
            echo "✅ Test course created\n";
        }
    } catch (Exception $e) {
        echo "⚠️  Florida courses table doesn't exist, creating basic courses table...\n";
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS courses (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                title varchar(255) NOT NULL,
                description text,
                state_code varchar(10) DEFAULT 'FL',
                is_active tinyint(1) DEFAULT 1,
                created_at timestamp NULL DEFAULT NULL,
                updated_at timestamp NULL DEFAULT NULL,
                PRIMARY KEY (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        
        $pdo->exec("
            INSERT INTO courses (title, description, state_code, is_active, created_at, updated_at) 
            VALUES ('Florida Traffic School Course', 'Basic traffic school course for Florida', 'FL', 1, NOW(), NOW())
        ");
        echo "✅ Basic courses table and test course created\n";
    }
    
    // Insert test enrollments
    echo "Creating test enrollments...\n";
    $pdo->exec("
        INSERT INTO user_course_enrollments 
        (user_id, course_id, course_table, payment_status, status, progress_percentage, enrolled_at, completed_at, certificate_generated_at, certificate_number, certificate_path, created_at, updated_at) 
        VALUES 
        (1, 1, 'florida_courses', 'paid', 'completed', 100.00, NOW(), NOW(), NOW(), 'CERT-2026-000001', 'certificates/cert-1.html', NOW(), NOW()),
        (1, 1, 'courses', 'paid', 'completed', 100.00, NOW(), NOW(), NOW(), 'CERT-2026-000002', 'certificates/cert-2.html', NOW(), NOW())
    ");
    
    // Verify data
    $enrollmentCount = $pdo->query("SELECT COUNT(*) FROM user_course_enrollments")->fetchColumn();
    echo "✅ Created {$enrollmentCount} test enrollments\n";
    
    // Test Laravel connection
    echo "\nTesting Laravel database connection...\n";
    $laravelCount = DB::table('user_course_enrollments')->count();
    echo "✅ Laravel can see {$laravelCount} enrollments\n";
    
    echo "\n🎉 EMERGENCY FIX COMPLETE!\n";
    echo "==========================\n";
    echo "✅ Table created successfully\n";
    echo "✅ Test data inserted\n";
    echo "✅ Laravel connection verified\n";
    echo "✅ System ready for certificate generation\n";
    
    echo "\n📋 TEST URLS:\n";
    echo "- Generate Certificates: /generate-certificates\n";
    echo "- My Certificates: /my-certificates.php\n";
    echo "- Test Certificates: /test-certificates.php\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n🏁 Fix completed at " . date('Y-m-d H:i:s') . "\n";