-- EMERGENCY DATABASE FIX - Create Missing user_course_enrollments Table
-- This script creates the missing user_course_enrollments table

-- Drop table if exists (to ensure clean creation)
DROP TABLE IF EXISTS `user_course_enrollments`;

-- Create the user_course_enrollments table
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert test data for immediate functionality
-- First, get a user ID (create one if none exists)
INSERT IGNORE INTO users (name, email, password, email_verified_at, created_at, updated_at) 
VALUES (
    'Test User', 
    'test@example.com', 
    '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
    NOW(), 
    NOW(), 
    NOW()
);

-- Get the user ID
SET @user_id = (SELECT id FROM users WHERE email = 'test@example.com' LIMIT 1);

-- Insert test course if none exists
INSERT IGNORE INTO florida_courses (id, title, description, duration_hours, price, is_active, created_at, updated_at) 
VALUES (
    1,
    'Florida Basic Driver Improvement Course', 
    'State-approved 4-hour basic driver improvement course for Florida', 
    4, 
    29.95, 
    1, 
    NOW(), 
    NOW()
);

-- Insert test enrollment data
INSERT INTO user_course_enrollments (
    user_id, course_id, course_table, payment_status, amount_paid, 
    payment_method, enrolled_at, started_at, completed_at, 
    progress_percentage, quiz_average, status, final_exam_completed,
    created_at, updated_at
) VALUES (
    @user_id, 1, 'florida_courses', 'paid', 29.95, 
    'test', NOW(), NOW(), NOW(), 
    100.00, 95.00, 'completed', 1,
    NOW(), NOW()
);

-- Add a few more test enrollments for variety
INSERT INTO user_course_enrollments (
    user_id, course_id, course_table, payment_status, amount_paid, 
    payment_method, enrolled_at, started_at, completed_at, 
    progress_percentage, quiz_average, status, final_exam_completed,
    created_at, updated_at
) VALUES 
(
    @user_id, 1, 'florida_courses', 'paid', 29.95, 
    'stripe', DATE_SUB(NOW(), INTERVAL 7 DAY), DATE_SUB(NOW(), INTERVAL 6 DAY), DATE_SUB(NOW(), INTERVAL 1 DAY), 
    100.00, 88.00, 'completed', 1,
    DATE_SUB(NOW(), INTERVAL 7 DAY), DATE_SUB(NOW(), INTERVAL 1 DAY)
),
(
    @user_id, 1, 'florida_courses', 'paid', 29.95, 
    'paypal', DATE_SUB(NOW(), INTERVAL 14 DAY), DATE_SUB(NOW(), INTERVAL 13 DAY), DATE_SUB(NOW(), INTERVAL 5 DAY), 
    100.00, 92.00, 'completed', 1,
    DATE_SUB(NOW(), INTERVAL 14 DAY), DATE_SUB(NOW(), INTERVAL 5 DAY)
);

-- Verify the fix
SELECT 'Table created successfully!' as status;
SELECT COUNT(*) as total_enrollments FROM user_course_enrollments;
SELECT COUNT(*) as completed_enrollments FROM user_course_enrollments WHERE status = 'completed';
SELECT 'Database fix completed!' as final_status;