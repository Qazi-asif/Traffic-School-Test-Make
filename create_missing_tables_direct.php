<?php

/**
 * Direct Table Creation Script
 * 
 * This script creates the missing tables directly using SQL commands
 * to ensure they exist before testing the system.
 */

echo "🔧 CREATING MISSING TABLES DIRECTLY\n";
echo str_repeat("=", 50) . "\n\n";

try {
    // Database connection
    $pdo = new PDO(
        "mysql:host=127.0.0.1;dbname=nelly-elearning", 
        "root", 
        "", 
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "✅ Database connection successful\n\n";
    
    // Create user_course_enrollments table
    echo "📋 Creating user_course_enrollments table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `user_course_enrollments` (
            `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            `user_id` bigint(20) unsigned NOT NULL,
            `course_id` bigint(20) unsigned NOT NULL,
            `course_table` varchar(255) NOT NULL DEFAULT 'florida_courses',
            `status` enum('enrolled','in_progress','completed','failed','suspended') NOT NULL DEFAULT 'enrolled',
            `payment_status` enum('pending','paid','failed','refunded') NOT NULL DEFAULT 'pending',
            `progress_percentage` decimal(5,2) NOT NULL DEFAULT 0.00,
            `enrolled_at` timestamp NULL DEFAULT NULL,
            `started_at` timestamp NULL DEFAULT NULL,
            `completed_at` timestamp NULL DEFAULT NULL,
            `final_score` decimal(5,2) NULL DEFAULT NULL,
            `attempts` int NOT NULL DEFAULT 0,
            `quiz_scores` json NULL DEFAULT NULL,
            `notes` text NULL DEFAULT NULL,
            `created_at` timestamp NULL DEFAULT NULL,
            `updated_at` timestamp NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `user_course_enrollments_user_id_course_id_course_table_index` (`user_id`,`course_id`,`course_table`),
            KEY `user_course_enrollments_status_payment_status_index` (`status`,`payment_status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✅ user_course_enrollments table created\n";
    
    // Create chapters table
    echo "📋 Creating chapters table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `chapters` (
            `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            `course_id` bigint(20) unsigned NOT NULL,
            `course_table` varchar(255) NOT NULL DEFAULT 'florida_courses',
            `title` varchar(255) NOT NULL,
            `content` longtext NULL DEFAULT NULL,
            `duration` int NOT NULL DEFAULT 0,
            `required_min_time` int NOT NULL DEFAULT 0,
            `order_index` int NOT NULL DEFAULT 0,
            `video_url` varchar(255) NULL DEFAULT NULL,
            `is_active` tinyint(1) NOT NULL DEFAULT 1,
            `has_quiz` tinyint(1) NOT NULL DEFAULT 0,
            `quiz_questions_count` int NOT NULL DEFAULT 0,
            `created_at` timestamp NULL DEFAULT NULL,
            `updated_at` timestamp NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `chapters_course_id_course_table_order_index_index` (`course_id`,`course_table`,`order_index`),
            KEY `chapters_is_active_index` (`is_active`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✅ chapters table created\n";
    
    // Create questions table
    echo "📋 Creating questions table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `questions` (
            `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            `chapter_id` bigint(20) unsigned NULL DEFAULT NULL,
            `course_id` bigint(20) unsigned NOT NULL,
            `question_text` text NOT NULL,
            `question_type` enum('multiple_choice','true_false','fill_blank','essay') NOT NULL DEFAULT 'multiple_choice',
            `options` json NULL DEFAULT NULL,
            `correct_answer` text NOT NULL,
            `explanation` text NULL DEFAULT NULL,
            `points` int NOT NULL DEFAULT 1,
            `order_index` int NOT NULL DEFAULT 0,
            `is_active` tinyint(1) NOT NULL DEFAULT 1,
            `created_at` timestamp NULL DEFAULT NULL,
            `updated_at` timestamp NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `questions_chapter_id_course_id_is_active_index` (`chapter_id`,`course_id`,`is_active`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✅ questions table created\n";
    
    // Create final_exam_questions table
    echo "📋 Creating final_exam_questions table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `final_exam_questions` (
            `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            `course_id` bigint(20) unsigned NOT NULL,
            `question_text` text NOT NULL,
            `question_type` enum('multiple_choice','true_false','fill_blank') NOT NULL DEFAULT 'multiple_choice',
            `options` json NULL DEFAULT NULL,
            `correct_answer` text NOT NULL,
            `explanation` text NULL DEFAULT NULL,
            `points` int NOT NULL DEFAULT 1,
            `order_index` int NOT NULL DEFAULT 0,
            `is_active` tinyint(1) NOT NULL DEFAULT 1,
            `created_at` timestamp NULL DEFAULT NULL,
            `updated_at` timestamp NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `final_exam_questions_course_id_is_active_index` (`course_id`,`is_active`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✅ final_exam_questions table created\n";
    
    // Create user_course_progress table
    echo "📋 Creating user_course_progress table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `user_course_progress` (
            `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            `enrollment_id` bigint(20) unsigned NOT NULL,
            `chapter_id` bigint(20) unsigned NOT NULL,
            `progress_percentage` decimal(5,2) NOT NULL DEFAULT 0.00,
            `time_spent` int NOT NULL DEFAULT 0,
            `started_at` timestamp NULL DEFAULT NULL,
            `completed_at` timestamp NULL DEFAULT NULL,
            `created_at` timestamp NULL DEFAULT NULL,
            `updated_at` timestamp NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `user_course_progress_enrollment_id_chapter_id_unique` (`enrollment_id`,`chapter_id`),
            KEY `user_course_progress_enrollment_id_progress_percentage_index` (`enrollment_id`,`progress_percentage`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✅ user_course_progress table created\n";
    
    // Create state course tables
    echo "\n📋 Creating state-specific course tables...\n";
    
    // Missouri courses
    echo "📋 Creating missouri_courses table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `missouri_courses` (
            `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            `course_id` bigint(20) unsigned NOT NULL,
            `missouri_course_code` varchar(255) NOT NULL,
            `course_type` enum('defensive_driving','point_reduction','dui_education') NOT NULL DEFAULT 'defensive_driving',
            `form_4444_template` varchar(255) NULL DEFAULT NULL,
            `requires_form_4444` tinyint(1) NOT NULL DEFAULT 1,
            `required_hours` decimal(4,2) NOT NULL DEFAULT 8.00,
            `max_completion_days` int NOT NULL DEFAULT 90,
            `approval_number` varchar(255) NULL DEFAULT NULL,
            `approved_date` date NULL DEFAULT NULL,
            `expiration_date` date NULL DEFAULT NULL,
            `is_active` tinyint(1) NOT NULL DEFAULT 1,
            `created_at` timestamp NULL DEFAULT NULL,
            `updated_at` timestamp NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `missouri_courses_missouri_course_code_course_type_is_active_index` (`missouri_course_code`,`course_type`,`is_active`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✅ missouri_courses table created\n";
    
    // Texas courses
    echo "📋 Creating texas_courses table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `texas_courses` (
            `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            `course_id` bigint(20) unsigned NOT NULL,
            `texas_course_code` varchar(255) NOT NULL,
            `tdlr_course_id` varchar(255) NULL DEFAULT NULL,
            `course_type` enum('defensive_driving','driving_safety','dui_education') NOT NULL DEFAULT 'defensive_driving',
            `requires_proctoring` tinyint(1) NOT NULL DEFAULT 0,
            `defensive_driving_hours` int NOT NULL DEFAULT 6,
            `required_hours` decimal(4,2) NOT NULL DEFAULT 6.00,
            `max_completion_days` int NOT NULL DEFAULT 90,
            `approval_number` varchar(255) NULL DEFAULT NULL,
            `approved_date` date NULL DEFAULT NULL,
            `expiration_date` date NULL DEFAULT NULL,
            `certificate_template` varchar(255) NULL DEFAULT NULL,
            `is_active` tinyint(1) NOT NULL DEFAULT 1,
            `created_at` timestamp NULL DEFAULT NULL,
            `updated_at` timestamp NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `texas_courses_texas_course_code_tdlr_course_id_course_type_is_active_index` (`texas_course_code`,`tdlr_course_id`,`course_type`,`is_active`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✅ texas_courses table created\n";
    
    // Delaware courses
    echo "📋 Creating delaware_courses table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `delaware_courses` (
            `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            `course_id` bigint(20) unsigned NOT NULL,
            `delaware_course_code` varchar(255) NOT NULL,
            `course_type` enum('defensive_driving','point_reduction','dui_education') NOT NULL DEFAULT 'defensive_driving',
            `required_hours` decimal(4,2) NOT NULL DEFAULT 8.00,
            `max_completion_days` int NOT NULL DEFAULT 90,
            `approval_number` varchar(255) NULL DEFAULT NULL,
            `approved_date` date NULL DEFAULT NULL,
            `expiration_date` date NULL DEFAULT NULL,
            `certificate_template` varchar(255) NULL DEFAULT NULL,
            `quiz_rotation_enabled` tinyint(1) NOT NULL DEFAULT 1,
            `quiz_pool_size` int NOT NULL DEFAULT 50,
            `is_active` tinyint(1) NOT NULL DEFAULT 1,
            `created_at` timestamp NULL DEFAULT NULL,
            `updated_at` timestamp NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `delaware_courses_delaware_course_code_course_type_is_active_index` (`delaware_course_code`,`course_type`,`is_active`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✅ delaware_courses table created\n";
    
    // Nevada courses
    echo "📋 Creating nevada_courses table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `nevada_courses` (
            `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            `course_id` bigint(20) unsigned NOT NULL,
            `nevada_course_code` varchar(255) NOT NULL,
            `course_type` enum('defensive_driving','traffic_safety','dui_education') NOT NULL DEFAULT 'defensive_driving',
            `required_hours` decimal(4,2) NOT NULL DEFAULT 4.00,
            `max_completion_days` int NOT NULL DEFAULT 90,
            `approval_number` varchar(255) NULL DEFAULT NULL,
            `approved_date` date NULL DEFAULT NULL,
            `expiration_date` date NULL DEFAULT NULL,
            `certificate_template` varchar(255) NULL DEFAULT NULL,
            `ntsa_enabled` tinyint(1) NOT NULL DEFAULT 0,
            `ntsa_court_name` varchar(255) NULL DEFAULT NULL,
            `is_active` tinyint(1) NOT NULL DEFAULT 1,
            `created_at` timestamp NULL DEFAULT NULL,
            `updated_at` timestamp NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `nevada_courses_nevada_course_code_course_type_is_active_index` (`nevada_course_code`,`course_type`,`is_active`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✅ nevada_courses table created\n";
    
    // Add sample data
    echo "\n📋 Adding sample data...\n";
    
    // Add sample enrollments for existing Florida courses
    $floridaCourses = $pdo->query("SELECT id FROM florida_courses LIMIT 3")->fetchAll(PDO::FETCH_COLUMN);
    
    if (count($floridaCourses) > 0) {
        foreach ($floridaCourses as $courseId) {
            // Create sample enrollment
            $pdo->exec("
                INSERT IGNORE INTO user_course_enrollments 
                (user_id, course_id, course_table, status, payment_status, progress_percentage, enrolled_at, started_at, created_at, updated_at)
                VALUES 
                (1, {$courseId}, 'florida_courses', 'in_progress', 'paid', 45.50, NOW(), NOW(), NOW(), NOW())
            ");
            
            // Create sample chapters
            $pdo->exec("
                INSERT IGNORE INTO chapters 
                (course_id, course_table, title, content, duration, required_min_time, order_index, is_active, has_quiz, created_at, updated_at)
                VALUES 
                ({$courseId}, 'florida_courses', 'Introduction to Defensive Driving', 'This chapter covers the basics of defensive driving.', 30, 25, 1, 1, 1, NOW(), NOW()),
                ({$courseId}, 'florida_courses', 'Traffic Laws and Safety', 'Understanding traffic laws and safety regulations.', 45, 40, 2, 1, 1, NOW(), NOW()),
                ({$courseId}, 'florida_courses', 'Hazard Recognition', 'Learning to identify and respond to road hazards.', 35, 30, 3, 1, 1, NOW(), NOW())
            ");
        }
        echo "✅ Sample enrollments and chapters created\n";
    }
    
    // Add sample courses to other state tables
    echo "📋 Adding sample courses to state tables...\n";
    
    // Create sample courses in main courses table first
    $sampleCourses = [
        ['title' => 'Missouri Defensive Driving Course', 'state' => 'Missouri', 'state_code' => 'MO', 'duration' => 480, 'price' => 29.95],
        ['title' => 'Texas Defensive Driving Course', 'state' => 'Texas', 'state_code' => 'TX', 'duration' => 360, 'price' => 25.00],
        ['title' => 'Delaware Defensive Driving Course', 'state' => 'Delaware', 'state_code' => 'DE', 'duration' => 480, 'price' => 35.00]
    ];
    
    foreach ($sampleCourses as $course) {
        // Insert into courses table
        $stmt = $pdo->prepare("
            INSERT IGNORE INTO courses 
            (title, description, state, state_code, duration, price, passing_score, is_active, course_type, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, 80, 1, 'defensive_driving', NOW(), NOW())
        ");
        $stmt->execute([
            $course['title'],
            "State-approved defensive driving course for {$course['state']} residents.",
            $course['state'],
            $course['state_code'],
            $course['duration'],
            $course['price']
        ]);
        
        $courseId = $pdo->lastInsertId();
        
        if ($courseId > 0) {
            // Insert into appropriate state table
            if ($course['state_code'] === 'MO') {
                $pdo->exec("
                    INSERT IGNORE INTO missouri_courses 
                    (course_id, missouri_course_code, course_type, required_hours, is_active, created_at, updated_at)
                    VALUES ({$courseId}, 'MO-{$courseId}', 'defensive_driving', 8.0, 1, NOW(), NOW())
                ");
            } elseif ($course['state_code'] === 'TX') {
                $pdo->exec("
                    INSERT IGNORE INTO texas_courses 
                    (course_id, texas_course_code, course_type, defensive_driving_hours, required_hours, is_active, created_at, updated_at)
                    VALUES ({$courseId}, 'TX-{$courseId}', 'defensive_driving', 6, 6.0, 1, NOW(), NOW())
                ");
            } elseif ($course['state_code'] === 'DE') {
                $pdo->exec("
                    INSERT IGNORE INTO delaware_courses 
                    (course_id, delaware_course_code, course_type, required_hours, quiz_rotation_enabled, is_active, created_at, updated_at)
                    VALUES ({$courseId}, 'DE-{$courseId}', 'defensive_driving', 8.0, 1, 1, NOW(), NOW())
                ");
            }
            
            echo "✅ Created {$course['state']} course (ID: {$courseId})\n";
        }
    }
    
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "🎉 ALL TABLES CREATED SUCCESSFULLY!\n";
    echo str_repeat("=", 50) . "\n\n";
    
    // Verify tables
    echo "📊 VERIFICATION:\n";
    $tables = [
        'user_course_enrollments',
        'chapters', 
        'questions',
        'final_exam_questions',
        'user_course_progress',
        'missouri_courses',
        'texas_courses', 
        'delaware_courses',
        'nevada_courses'
    ];
    
    foreach ($tables as $table) {
        $count = $pdo->query("SELECT COUNT(*) FROM {$table}")->fetchColumn();
        echo "• {$table}: {$count} records\n";
    }
    
    echo "\n✅ System is now ready for testing!\n";
    echo "Visit: http://nelly-elearning.test/api/admin/analytics/state-distribution\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

?>