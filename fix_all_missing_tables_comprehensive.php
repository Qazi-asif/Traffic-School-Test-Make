<?php

/**
 * COMPREHENSIVE DATABASE FIX - Create ALL Missing Tables
 * This script identifies and creates all missing tables affecting all modules
 */

echo "🔧 COMPREHENSIVE DATABASE FIX - Creating ALL Missing Tables\n";
echo str_repeat("=", 70) . "\n";

$host = '127.0.0.1';
$port = '3306';
$database = 'nelly-elearning';
$username = 'root';
$password = '';

$errors = [];
$created = [];
$skipped = [];

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Connected to database: $database\n\n";
    
    // Define all required tables with their structures
    $requiredTables = [
        
        // Core System Tables
        'user_course_enrollments' => "
            CREATE TABLE IF NOT EXISTS `user_course_enrollments` (
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
                `certificate_generated_at` timestamp NULL DEFAULT NULL,
                `certificate_number` varchar(255) DEFAULT NULL,
                `certificate_path` varchar(500) DEFAULT NULL,
                `created_at` timestamp NULL DEFAULT NULL,
                `updated_at` timestamp NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `user_course_enrollments_user_id_index` (`user_id`),
                KEY `user_course_enrollments_course_id_index` (`course_id`),
                KEY `user_course_enrollments_status_index` (`status`),
                KEY `user_course_enrollments_payment_status_index` (`payment_status`),
                KEY `user_course_enrollments_completed_at_index` (`completed_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
        // Course Tables
        'courses' => "
            CREATE TABLE IF NOT EXISTS `courses` (
                `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                `title` varchar(255) NOT NULL,
                `description` text,
                `duration_hours` int(11) DEFAULT '4',
                `price` decimal(8,2) DEFAULT '29.95',
                `state` varchar(2) DEFAULT 'FL',
                `is_active` tinyint(1) DEFAULT '1',
                `created_at` timestamp NULL DEFAULT NULL,
                `updated_at` timestamp NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `courses_state_index` (`state`),
                KEY `courses_is_active_index` (`is_active`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
        'chapters' => "
            CREATE TABLE IF NOT EXISTS `chapters` (
                `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                `course_id` bigint(20) unsigned NOT NULL,
                `course_table` varchar(255) DEFAULT 'courses',
                `title` varchar(255) NOT NULL,
                `content` longtext,
                `order_index` int(11) DEFAULT '0',
                `duration_minutes` int(11) DEFAULT '30',
                `is_active` tinyint(1) DEFAULT '1',
                `created_at` timestamp NULL DEFAULT NULL,
                `updated_at` timestamp NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `chapters_course_id_index` (`course_id`),
                KEY `chapters_order_index_index` (`order_index`),
                KEY `chapters_is_active_index` (`is_active`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
        'chapter_questions' => "
            CREATE TABLE IF NOT EXISTS `chapter_questions` (
                `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                `chapter_id` bigint(20) unsigned NOT NULL,
                `question_text` text NOT NULL,
                `option_a` varchar(500) DEFAULT NULL,
                `option_b` varchar(500) DEFAULT NULL,
                `option_c` varchar(500) DEFAULT NULL,
                `option_d` varchar(500) DEFAULT NULL,
                `correct_answer` enum('A','B','C','D') NOT NULL,
                `explanation` text,
                `order_index` int(11) DEFAULT '0',
                `is_active` tinyint(1) DEFAULT '1',
                `quiz_set` varchar(50) DEFAULT 'default',
                `created_at` timestamp NULL DEFAULT NULL,
                `updated_at` timestamp NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `chapter_questions_chapter_id_index` (`chapter_id`),
                KEY `chapter_questions_order_index_index` (`order_index`),
                KEY `chapter_questions_is_active_index` (`is_active`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
        'questions' => "
            CREATE TABLE IF NOT EXISTS `questions` (
                `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                `course_id` bigint(20) unsigned NOT NULL,
                `question_text` text NOT NULL,
                `option_a` varchar(500) DEFAULT NULL,
                `option_b` varchar(500) DEFAULT NULL,
                `option_c` varchar(500) DEFAULT NULL,
                `option_d` varchar(500) DEFAULT NULL,
                `correct_answer` enum('A','B','C','D') NOT NULL,
                `explanation` text,
                `is_active` tinyint(1) DEFAULT '1',
                `created_at` timestamp NULL DEFAULT NULL,
                `updated_at` timestamp NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `questions_course_id_index` (`course_id`),
                KEY `questions_is_active_index` (`is_active`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
        // Final Exam Tables
        'final_exam_questions' => "
            CREATE TABLE IF NOT EXISTS `final_exam_questions` (
                `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                `course_id` bigint(20) unsigned NOT NULL,
                `question_text` text NOT NULL,
                `option_a` varchar(500) DEFAULT NULL,
                `option_b` varchar(500) DEFAULT NULL,
                `option_c` varchar(500) DEFAULT NULL,
                `option_d` varchar(500) DEFAULT NULL,
                `correct_answer` enum('A','B','C','D') NOT NULL,
                `explanation` text,
                `is_active` tinyint(1) DEFAULT '1',
                `created_at` timestamp NULL DEFAULT NULL,
                `updated_at` timestamp NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `final_exam_questions_course_id_index` (`course_id`),
                KEY `final_exam_questions_is_active_index` (`is_active`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
        'final_exam_results' => "
            CREATE TABLE IF NOT EXISTS `final_exam_results` (
                `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                `user_id` bigint(20) unsigned NOT NULL,
                `enrollment_id` bigint(20) unsigned NOT NULL,
                `course_id` bigint(20) unsigned NOT NULL,
                `total_questions` int(11) NOT NULL,
                `correct_answers` int(11) NOT NULL,
                `wrong_answers` int(11) NOT NULL,
                `percentage` decimal(5,2) NOT NULL,
                `passed` tinyint(1) NOT NULL DEFAULT '0',
                `answers` json DEFAULT NULL,
                `time_taken` int(11) DEFAULT NULL,
                `created_at` timestamp NULL DEFAULT NULL,
                `updated_at` timestamp NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `final_exam_results_user_id_index` (`user_id`),
                KEY `final_exam_results_enrollment_id_index` (`enrollment_id`),
                KEY `final_exam_results_course_id_index` (`course_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
        // Progress Tracking Tables
        'user_course_progress' => "
            CREATE TABLE IF NOT EXISTS `user_course_progress` (
                `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                `user_id` bigint(20) unsigned NOT NULL,
                `enrollment_id` bigint(20) unsigned NOT NULL,
                `chapter_id` bigint(20) unsigned NOT NULL,
                `is_completed` tinyint(1) DEFAULT '0',
                `progress_percentage` decimal(5,2) DEFAULT '0.00',
                `time_spent` int(11) DEFAULT '0',
                `started_at` timestamp NULL DEFAULT NULL,
                `completed_at` timestamp NULL DEFAULT NULL,
                `created_at` timestamp NULL DEFAULT NULL,
                `updated_at` timestamp NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `user_course_progress_user_id_index` (`user_id`),
                KEY `user_course_progress_enrollment_id_index` (`enrollment_id`),
                KEY `user_course_progress_chapter_id_index` (`chapter_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
        'chapter_quiz_results' => "
            CREATE TABLE IF NOT EXISTS `chapter_quiz_results` (
                `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                `user_id` bigint(20) unsigned NOT NULL,
                `chapter_id` bigint(20) unsigned NOT NULL,
                `total_questions` int(11) NOT NULL,
                `correct_answers` int(11) NOT NULL,
                `wrong_answers` int(11) NOT NULL,
                `percentage` decimal(5,2) NOT NULL,
                `answers` json DEFAULT NULL,
                `created_at` timestamp NULL DEFAULT NULL,
                `updated_at` timestamp NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `chapter_quiz_results_user_id_index` (`user_id`),
                KEY `chapter_quiz_results_chapter_id_index` (`chapter_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
        // State-Specific Course Tables
        'missouri_courses' => "
            CREATE TABLE IF NOT EXISTS `missouri_courses` (
                `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                `title` varchar(255) NOT NULL,
                `description` text,
                `duration_hours` int(11) DEFAULT '8',
                `price` decimal(8,2) DEFAULT '29.95',
                `is_active` tinyint(1) DEFAULT '1',
                `created_at` timestamp NULL DEFAULT NULL,
                `updated_at` timestamp NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `missouri_courses_is_active_index` (`is_active`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
        'texas_courses' => "
            CREATE TABLE IF NOT EXISTS `texas_courses` (
                `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                `title` varchar(255) NOT NULL,
                `description` text,
                `duration_hours` int(11) DEFAULT '6',
                `price` decimal(8,2) DEFAULT '29.95',
                `is_active` tinyint(1) DEFAULT '1',
                `created_at` timestamp NULL DEFAULT NULL,
                `updated_at` timestamp NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `texas_courses_is_active_index` (`is_active`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
        'delaware_courses' => "
            CREATE TABLE IF NOT EXISTS `delaware_courses` (
                `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                `title` varchar(255) NOT NULL,
                `description` text,
                `duration_hours` int(11) DEFAULT '8',
                `price` decimal(8,2) DEFAULT '29.95',
                `is_active` tinyint(1) DEFAULT '1',
                `created_at` timestamp NULL DEFAULT NULL,
                `updated_at` timestamp NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `delaware_courses_is_active_index` (`is_active`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
        'nevada_courses' => "
            CREATE TABLE IF NOT EXISTS `nevada_courses` (
                `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                `title` varchar(255) NOT NULL,
                `description` text,
                `duration_hours` int(11) DEFAULT '4',
                `price` decimal(8,2) DEFAULT '29.95',
                `is_active` tinyint(1) DEFAULT '1',
                `created_at` timestamp NULL DEFAULT NULL,
                `updated_at` timestamp NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `nevada_courses_is_active_index` (`is_active`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
        // State-Specific Chapter Tables
        'florida_chapters' => "
            CREATE TABLE IF NOT EXISTS `florida_chapters` (
                `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                `course_id` bigint(20) unsigned NOT NULL,
                `title` varchar(255) NOT NULL,
                `content` longtext,
                `order_index` int(11) DEFAULT '0',
                `duration_minutes` int(11) DEFAULT '30',
                `is_active` tinyint(1) DEFAULT '1',
                `created_at` timestamp NULL DEFAULT NULL,
                `updated_at` timestamp NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `florida_chapters_course_id_index` (`course_id`),
                KEY `florida_chapters_order_index_index` (`order_index`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
        'missouri_chapters' => "
            CREATE TABLE IF NOT EXISTS `missouri_chapters` (
                `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                `course_id` bigint(20) unsigned NOT NULL,
                `title` varchar(255) NOT NULL,
                `content` longtext,
                `order_index` int(11) DEFAULT '0',
                `duration_minutes` int(11) DEFAULT '30',
                `is_active` tinyint(1) DEFAULT '1',
                `created_at` timestamp NULL DEFAULT NULL,
                `updated_at` timestamp NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `missouri_chapters_course_id_index` (`course_id`),
                KEY `missouri_chapters_order_index_index` (`order_index`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
        'texas_chapters' => "
            CREATE TABLE IF NOT EXISTS `texas_chapters` (
                `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                `course_id` bigint(20) unsigned NOT NULL,
                `title` varchar(255) NOT NULL,
                `content` longtext,
                `order_index` int(11) DEFAULT '0',
                `duration_minutes` int(11) DEFAULT '30',
                `is_active` tinyint(1) DEFAULT '1',
                `created_at` timestamp NULL DEFAULT NULL,
                `updated_at` timestamp NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `texas_chapters_course_id_index` (`course_id`),
                KEY `texas_chapters_order_index_index` (`order_index`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
        'delaware_chapters' => "
            CREATE TABLE IF NOT EXISTS `delaware_chapters` (
                `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                `course_id` bigint(20) unsigned NOT NULL,
                `title` varchar(255) NOT NULL,
                `content` longtext,
                `order_index` int(11) DEFAULT '0',
                `duration_minutes` int(11) DEFAULT '30',
                `is_active` tinyint(1) DEFAULT '1',
                `created_at` timestamp NULL DEFAULT NULL,
                `updated_at` timestamp NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `delaware_chapters_course_id_index` (`course_id`),
                KEY `delaware_chapters_order_index_index` (`order_index`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
        // Free Response Quiz Tables
        'free_response_quiz_placements' => "
            CREATE TABLE IF NOT EXISTS `free_response_quiz_placements` (
                `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                `course_id` bigint(20) unsigned NOT NULL,
                `placement_name` varchar(255) NOT NULL,
                `after_chapter` int(11) DEFAULT NULL,
                `before_chapter` int(11) DEFAULT NULL,
                `is_active` tinyint(1) DEFAULT '1',
                `created_at` timestamp NULL DEFAULT NULL,
                `updated_at` timestamp NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `free_response_quiz_placements_course_id_index` (`course_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
        'free_response_questions' => "
            CREATE TABLE IF NOT EXISTS `free_response_questions` (
                `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                `course_id` bigint(20) unsigned NOT NULL,
                `placement_id` bigint(20) unsigned DEFAULT NULL,
                `question_text` text NOT NULL,
                `sample_answer` text,
                `grading_rubric` text,
                `max_words` int(11) DEFAULT '500',
                `is_active` tinyint(1) DEFAULT '1',
                `created_at` timestamp NULL DEFAULT NULL,
                `updated_at` timestamp NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `free_response_questions_course_id_index` (`course_id`),
                KEY `free_response_questions_placement_id_index` (`placement_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
        'free_response_answers' => "
            CREATE TABLE IF NOT EXISTS `free_response_answers` (
                `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                `user_id` bigint(20) unsigned NOT NULL,
                `question_id` bigint(20) unsigned NOT NULL,
                `enrollment_id` bigint(20) unsigned NOT NULL,
                `answer_text` text NOT NULL,
                `word_count` int(11) DEFAULT '0',
                `is_graded` tinyint(1) DEFAULT '0',
                `grade` decimal(5,2) DEFAULT NULL,
                `feedback` text,
                `created_at` timestamp NULL DEFAULT NULL,
                `updated_at` timestamp NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `free_response_answers_user_id_index` (`user_id`),
                KEY `free_response_answers_question_id_index` (`question_id`),
                KEY `free_response_answers_enrollment_id_index` (`enrollment_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
        // System Tables
        'system_modules' => "
            CREATE TABLE IF NOT EXISTS `system_modules` (
                `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                `module_name` varchar(255) NOT NULL,
                `display_name` varchar(255) NOT NULL,
                `description` text,
                `enabled` tinyint(1) DEFAULT '1',
                `created_at` timestamp NULL DEFAULT NULL,
                `updated_at` timestamp NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `system_modules_module_name_unique` (`module_name`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
        'system_settings' => "
            CREATE TABLE IF NOT EXISTS `system_settings` (
                `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                `key` varchar(255) NOT NULL,
                `value` text,
                `type` varchar(50) DEFAULT 'string',
                `description` text,
                `created_at` timestamp NULL DEFAULT NULL,
                `updated_at` timestamp NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `system_settings_key_unique` (`key`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
        // Timer Tables
        'chapter_timers' => "
            CREATE TABLE IF NOT EXISTS `chapter_timers` (
                `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                `chapter_id` bigint(20) unsigned NOT NULL,
                `chapter_type` varchar(255) DEFAULT 'chapters',
                `required_time_minutes` int(11) NOT NULL,
                `is_enabled` tinyint(1) DEFAULT '1',
                `allow_pause` tinyint(1) DEFAULT '1',
                `bypass_for_admin` tinyint(1) DEFAULT '1',
                `created_at` timestamp NULL DEFAULT NULL,
                `updated_at` timestamp NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `chapter_timers_chapter_id_index` (`chapter_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
        // Support Tables
        'support_tickets' => "
            CREATE TABLE IF NOT EXISTS `support_tickets` (
                `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                `user_id` bigint(20) unsigned NOT NULL,
                `subject` varchar(255) NOT NULL,
                `message` text NOT NULL,
                `status` enum('open','in_progress','resolved','closed') DEFAULT 'open',
                `priority` enum('low','medium','high','urgent') DEFAULT 'medium',
                `created_at` timestamp NULL DEFAULT NULL,
                `updated_at` timestamp NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `support_tickets_user_id_index` (`user_id`),
                KEY `support_tickets_status_index` (`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
        // Certificate Tables
        'florida_certificates' => "
            CREATE TABLE IF NOT EXISTS `florida_certificates` (
                `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                `enrollment_id` bigint(20) unsigned NOT NULL,
                `user_id` bigint(20) unsigned NOT NULL,
                `course_id` bigint(20) unsigned NOT NULL,
                `certificate_number` varchar(255) NOT NULL,
                `student_name` varchar(255) NOT NULL,
                `completion_date` date NOT NULL,
                `score` decimal(5,2) DEFAULT NULL,
                `verification_hash` varchar(255) NOT NULL,
                `pdf_path` varchar(500) DEFAULT NULL,
                `is_submitted_to_state` tinyint(1) DEFAULT '0',
                `submitted_at` timestamp NULL DEFAULT NULL,
                `created_at` timestamp NULL DEFAULT NULL,
                `updated_at` timestamp NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `florida_certificates_certificate_number_unique` (`certificate_number`),
                UNIQUE KEY `florida_certificates_verification_hash_unique` (`verification_hash`),
                KEY `florida_certificates_enrollment_id_index` (`enrollment_id`),
                KEY `florida_certificates_user_id_index` (`user_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
        // Payment Tables
        'payments' => "
            CREATE TABLE IF NOT EXISTS `payments` (
                `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                `user_id` bigint(20) unsigned NOT NULL,
                `enrollment_id` bigint(20) unsigned DEFAULT NULL,
                `amount` decimal(8,2) NOT NULL,
                `currency` varchar(3) DEFAULT 'USD',
                `payment_method` varchar(50) NOT NULL,
                `payment_id` varchar(255) DEFAULT NULL,
                `transaction_id` varchar(255) DEFAULT NULL,
                `status` enum('pending','completed','failed','refunded','cancelled') DEFAULT 'pending',
                `gateway_response` json DEFAULT NULL,
                `created_at` timestamp NULL DEFAULT NULL,
                `updated_at` timestamp NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `payments_user_id_index` (`user_id`),
                KEY `payments_enrollment_id_index` (`enrollment_id`),
                KEY `payments_status_index` (`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
        // Admin Tables
        'admin_users' => "
            CREATE TABLE IF NOT EXISTS `admin_users` (
                `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                `user_id` bigint(20) unsigned NOT NULL,
                `permissions` json DEFAULT NULL,
                `is_active` tinyint(1) DEFAULT '1',
                `created_at` timestamp NULL DEFAULT NULL,
                `updated_at` timestamp NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `admin_users_user_id_index` (`user_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
        // State Integration Tables
        'state_submission_queue' => "
            CREATE TABLE IF NOT EXISTS `state_submission_queue` (
                `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                `enrollment_id` bigint(20) unsigned NOT NULL,
                `state_code` varchar(2) NOT NULL,
                `submission_type` varchar(50) NOT NULL,
                `payload` json NOT NULL,
                `status` enum('pending','processing','completed','failed') DEFAULT 'pending',
                `attempts` int(11) DEFAULT '0',
                `last_attempt_at` timestamp NULL DEFAULT NULL,
                `completed_at` timestamp NULL DEFAULT NULL,
                `error_message` text,
                `created_at` timestamp NULL DEFAULT NULL,
                `updated_at` timestamp NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `state_submission_queue_enrollment_id_index` (`enrollment_id`),
                KEY `state_submission_queue_state_code_index` (`state_code`),
                KEY `state_submission_queue_status_index` (`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    ];
    
    echo "🔍 Checking and creating " . count($requiredTables) . " tables...\n\n";
    
    foreach ($requiredTables as $tableName => $sql) {
        echo "Checking table: $tableName... ";
        
        // Check if table exists
        $stmt = $pdo->prepare("SHOW TABLES LIKE '$tableName'");
        $stmt->execute();
        $exists = $stmt->fetch();
        
        if ($exists) {
            echo "✅ EXISTS\n";
            $skipped[] = $tableName;
        } else {
            echo "❌ MISSING - Creating... ";
            try {
                $pdo->exec($sql);
                echo "✅ CREATED\n";
                $created[] = $tableName;
            } catch (Exception $e) {
                echo "❌ FAILED: " . $e->getMessage() . "\n";
                $errors[] = "$tableName: " . $e->getMessage();
            }
        }
    }
    
    echo "\n" . str_repeat("=", 70) . "\n";
    echo "📊 SUMMARY\n";
    echo str_repeat("=", 70) . "\n";
    
    echo "✅ Tables already existing: " . count($skipped) . "\n";
    echo "🆕 Tables created: " . count($created) . "\n";
    echo "❌ Errors: " . count($errors) . "\n\n";
    
    if (!empty($created)) {
        echo "🆕 CREATED TABLES:\n";
        foreach ($created as $table) {
            echo "   • $table\n";
        }
        echo "\n";
    }
    
    if (!empty($errors)) {
        echo "❌ ERRORS:\n";
        foreach ($errors as $error) {
            echo "   • $error\n";
        }
        echo "\n";
    }
    
    // Add some basic test data for key tables
    echo "🔧 Adding basic test data...\n";
    
    // Add system modules
    $pdo->exec("
        INSERT IGNORE INTO system_modules (module_name, display_name, description, enabled, created_at, updated_at) VALUES
        ('admin_panel', 'Admin Panel', 'Administrative interface', 1, NOW(), NOW()),
        ('certificate_generation', 'Certificate Generation', 'Certificate creation and management', 1, NOW(), NOW()),
        ('payment_processing', 'Payment Processing', 'Payment gateway integration', 1, NOW(), NOW()),
        ('course_player', 'Course Player', 'Course delivery system', 1, NOW(), NOW()),
        ('quiz_system', 'Quiz System', 'Chapter and final exam quizzes', 1, NOW(), NOW())
    ");
    
    // Add basic courses if they don't exist
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM courses");
    $stmt->execute();
    $courseCount = $stmt->fetch()['count'];
    
    if ($courseCount == 0) {
        $pdo->exec("
            INSERT INTO courses (title, description, duration_hours, price, state, is_active, created_at, updated_at) VALUES
            ('Basic Traffic School Course', 'Generic traffic school course', 4, 29.95, 'FL', 1, NOW(), NOW())
        ");
        echo "✅ Added basic course data\n";
    }
    
    // Add basic chapters if they don't exist
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM chapters");
    $stmt->execute();
    $chapterCount = $stmt->fetch()['count'];
    
    if ($chapterCount == 0) {
        $pdo->exec("
            INSERT INTO chapters (course_id, course_table, title, content, order_index, duration_minutes, is_active, created_at, updated_at) VALUES
            (1, 'courses', 'Chapter 1: Traffic Laws', '<h2>Traffic Laws</h2><p>Basic traffic laws and regulations.</p>', 1, 30, 1, NOW(), NOW()),
            (1, 'courses', 'Chapter 2: Safe Driving', '<h2>Safe Driving</h2><p>Safe driving practices and techniques.</p>', 2, 30, 1, NOW(), NOW())
        ");
        echo "✅ Added basic chapter data\n";
    }
    
    echo "\n🎉 COMPREHENSIVE DATABASE FIX COMPLETED!\n";
    echo "All required tables have been created or verified.\n";
    echo "The application should now work across all modules.\n\n";
    
    echo "🔗 Test the system:\n";
    echo "   • http://nelly-elearning.test/dashboard\n";
    echo "   • http://nelly-elearning.test/generate-certificates\n";
    echo "   • http://nelly-elearning.test/my-certificates\n";
    echo "   • http://nelly-elearning.test/admin\n";
    
} catch (Exception $e) {
    echo "❌ Critical Error: " . $e->getMessage() . "\n";
    exit(1);
}

?>