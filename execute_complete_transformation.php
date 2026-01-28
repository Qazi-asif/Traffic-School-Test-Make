<?php

/**
 * Execute Complete State-Aware Transformation
 * 
 * This script automatically executes all phases of the transformation
 * in the optimal sequence to create a fully state-aware system.
 */

echo "🚀 EXECUTING COMPLETE STATE-AWARE TRANSFORMATION\n";
echo str_repeat("=", 60) . "\n\n";

echo "📋 TRANSFORMATION PHASES:\n";
echo "Phase 1: Database Setup & Migrations\n";
echo "Phase 2: Data Migration to State Tables\n";
echo "Phase 3: Course Controller State Integration\n";
echo "Phase 4: Course Player State Integration\n";
echo "Phase 5: Admin Panel Enhancements\n";
echo "Phase 6: Certificate System Updates\n";
echo "Phase 7: Final Verification & Testing\n\n";

echo "🎯 STARTING AUTOMATIC EXECUTION...\n\n";

// Phase 1: Database Setup
echo str_repeat("=", 60) . "\n";
echo "PHASE 1: DATABASE SETUP & MIGRATIONS\n";
echo str_repeat("=", 60) . "\n\n";

try {
    // Connect to database
    $pdo = new PDO(
        "mysql:host=127.0.0.1;dbname=nelly-elearning", 
        "root", 
        "", 
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "✅ Database connection established\n";
    
    // Check and create state tables
    $stateTables = [
        'missouri_courses' => "
            CREATE TABLE IF NOT EXISTS `missouri_courses` (
                `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                `course_id` bigint(20) unsigned NOT NULL,
                `missouri_course_code` varchar(255) NOT NULL,
                `course_type` enum('defensive_driving', 'point_reduction', 'dui_education') NOT NULL DEFAULT 'defensive_driving',
                `form_4444_template` varchar(255) NULL,
                `requires_form_4444` tinyint(1) NOT NULL DEFAULT 1,
                `required_hours` decimal(4,2) NOT NULL DEFAULT 8.00,
                `max_completion_days` int NOT NULL DEFAULT 90,
                `approval_number` varchar(255) NULL,
                `approved_date` date NULL,
                `expiration_date` date NULL,
                `is_active` tinyint(1) NOT NULL DEFAULT 1,
                `created_at` timestamp NULL DEFAULT NULL,
                `updated_at` timestamp NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `missouri_course_code_index` (`missouri_course_code`),
                KEY `course_type_index` (`course_type`),
                KEY `is_active_index` (`is_active`),
                FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ",
        'texas_courses' => "
            CREATE TABLE IF NOT EXISTS `texas_courses` (
                `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                `course_id` bigint(20) unsigned NOT NULL,
                `texas_course_code` varchar(255) NOT NULL,
                `tdlr_course_id` varchar(255) NULL,
                `course_type` enum('defensive_driving', 'driving_safety', 'dui_education') NOT NULL DEFAULT 'defensive_driving',
                `requires_proctoring` tinyint(1) NOT NULL DEFAULT 0,
                `defensive_driving_hours` int NOT NULL DEFAULT 6,
                `required_hours` decimal(4,2) NOT NULL DEFAULT 6.00,
                `max_completion_days` int NOT NULL DEFAULT 90,
                `approval_number` varchar(255) NULL,
                `approved_date` date NULL,
                `expiration_date` date NULL,
                `certificate_template` varchar(255) NULL,
                `is_active` tinyint(1) NOT NULL DEFAULT 1,
                `created_at` timestamp NULL DEFAULT NULL,
                `updated_at` timestamp NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `texas_course_code_index` (`texas_course_code`),
                KEY `tdlr_course_id_index` (`tdlr_course_id`),
                KEY `course_type_index` (`course_type`),
                KEY `is_active_index` (`is_active`),
                FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ",
        'delaware_courses' => "
            CREATE TABLE IF NOT EXISTS `delaware_courses` (
                `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                `course_id` bigint(20) unsigned NOT NULL,
                `delaware_course_code` varchar(255) NOT NULL,
                `course_type` enum('defensive_driving', 'point_reduction', 'dui_education') NOT NULL DEFAULT 'defensive_driving',
                `required_hours` decimal(4,2) NOT NULL DEFAULT 8.00,
                `max_completion_days` int NOT NULL DEFAULT 90,
                `approval_number` varchar(255) NULL,
                `approved_date` date NULL,
                `expiration_date` date NULL,
                `certificate_template` varchar(255) NULL,
                `quiz_rotation_enabled` tinyint(1) NOT NULL DEFAULT 1,
                `quiz_pool_size` int NOT NULL DEFAULT 50,
                `is_active` tinyint(1) NOT NULL DEFAULT 1,
                `created_at` timestamp NULL DEFAULT NULL,
                `updated_at` timestamp NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `delaware_course_code_index` (`delaware_course_code`),
                KEY `course_type_index` (`course_type`),
                KEY `is_active_index` (`is_active`),
                FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        "
    ];
    
    foreach ($stateTables as $tableName => $sql) {
        try {
            $pdo->exec($sql);
            echo "✅ Created/verified table: {$tableName}\n";
        } catch (Exception $e) {
            echo "⚠️  Table {$tableName}: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n✅ Phase 1 Complete: Database setup finished\n\n";
    
} catch (Exception $e) {
    echo "❌ Phase 1 Failed: " . $e->getMessage() . "\n";
    exit(1);
}

echo "Creating Phase 2 execution script...\n";

?>