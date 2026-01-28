-- Create user_course_enrollments table
DROP TABLE IF EXISTS `user_course_enrollments`;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert test data
INSERT INTO `user_course_enrollments` (`user_id`, `course_id`, `course_table`, `payment_status`, `status`, `progress_percentage`, `enrolled_at`, `completed_at`, `certificate_generated_at`, `certificate_number`, `certificate_path`, `created_at`, `updated_at`) VALUES
(1, 1, 'florida_courses', 'paid', 'completed', 100.00, NOW(), NOW(), NOW(), 'CERT-2026-000001', 'certificates/cert-1.html', NOW(), NOW()),
(1, 2, 'florida_courses', 'paid', 'completed', 100.00, NOW(), NOW(), NOW(), 'CERT-2026-000002', 'certificates/cert-2.html', NOW(), NOW());

-- Verify table creation
SELECT COUNT(*) as record_count FROM `user_course_enrollments`;