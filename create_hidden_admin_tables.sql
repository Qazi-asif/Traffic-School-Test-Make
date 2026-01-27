-- Create system_modules table for hidden admin panel
CREATE TABLE IF NOT EXISTS `system_modules` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `module_name` varchar(255) NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT 1,
  `updated_by` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `system_modules_module_name_unique` (`module_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create system_settings table for hidden admin panel
CREATE TABLE IF NOT EXISTS `system_settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) NOT NULL,
  `value` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `system_settings_key_unique` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default module states (all enabled by default)
INSERT IGNORE INTO `system_modules` (`module_name`, `enabled`, `created_at`, `updated_at`) VALUES
('user_registration', 1, NOW(), NOW()),
('course_enrollment', 1, NOW(), NOW()),
('payment_processing', 1, NOW(), NOW()),
('certificate_generation', 1, NOW(), NOW()),
('state_transmissions', 1, NOW(), NOW()),
('admin_panel', 1, NOW(), NOW()),
('announcements', 1, NOW(), NOW()),
('course_content', 1, NOW(), NOW()),
('student_feedback', 1, NOW(), NOW()),
('final_exams', 1, NOW(), NOW()),
('reports', 1, NOW(), NOW()),
('email_system', 1, NOW(), NOW()),
('support_tickets', 1, NOW(), NOW()),
('booklet_orders', 1, NOW(), NOW());

-- Insert default system settings
INSERT IGNORE INTO `system_settings` (`key`, `value`, `created_at`, `updated_at`) VALUES
('license_expires_at', NULL, NOW(), NOW()),
('license_id', 'DEMO_LICENSE_001', NOW(), NOW());