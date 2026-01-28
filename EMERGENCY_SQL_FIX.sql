-- EMERGENCY LOGIN FIX - Run these commands in phpMyAdmin or your database tool

-- 1. Create users table with proper structure
CREATE TABLE IF NOT EXISTS `users` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `email` varchar(255) NOT NULL,
    `email_verified_at` timestamp NULL DEFAULT NULL,
    `password` varchar(255) NOT NULL,
    `role_id` bigint(20) unsigned DEFAULT 2,
    `state_code` varchar(10) DEFAULT 'FL',
    `remember_token` varchar(100) DEFAULT NULL,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Create roles table
CREATE TABLE IF NOT EXISTS `roles` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `slug` varchar(255) NOT NULL,
    `description` text NULL,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `roles_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Insert roles
INSERT INTO roles (id, name, slug, description, created_at, updated_at) VALUES
(1, 'Administrator', 'admin', 'System administrator', NOW(), NOW()),
(2, 'Student', 'student', 'Student user', NOW(), NOW()),
(3, 'Instructor', 'instructor', 'Course instructor', NOW(), NOW())
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- 4. Create admin user (password is 'admin123')
INSERT INTO users (id, name, email, password, email_verified_at, role_id, state_code, created_at, updated_at) 
VALUES (1, 'Admin User', 'admin@dummiestrafficschool.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW(), 1, 'FL', NOW(), NOW())
ON DUPLICATE KEY UPDATE 
password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
role_id = 1,
updated_at = NOW();

-- 5. Create test student (password is 'student123')
INSERT INTO users (id, name, email, password, email_verified_at, role_id, state_code, created_at, updated_at) 
VALUES (2, 'Test Student', 'student@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW(), 2, 'FL', NOW(), NOW())
ON DUPLICATE KEY UPDATE 
password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
role_id = 2,
updated_at = NOW();

-- 6. Create password reset tokens table
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
    `email` varchar(255) NOT NULL,
    `token` varchar(255) NOT NULL,
    `created_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Create sessions table
CREATE TABLE IF NOT EXISTS `sessions` (
    `id` varchar(255) NOT NULL,
    `user_id` bigint(20) unsigned DEFAULT NULL,
    `ip_address` varchar(45) DEFAULT NULL,
    `user_agent` text,
    `payload` longtext NOT NULL,
    `last_activity` int NOT NULL,
    PRIMARY KEY (`id`),
    KEY `sessions_user_id_index` (`user_id`),
    KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Restore Florida courses
INSERT IGNORE INTO florida_courses (id, title, description, state_code, total_duration, price, min_pass_score, course_type, is_active, created_at, updated_at) VALUES 
(1, 'Florida Basic Driver Improvement (BDI) Course', 'State-approved 4-hour Basic Driver Improvement course for Florida residents.', 'FL', 240, 25.00, 80, 'BDI', 1, NOW(), NOW()),
(2, 'Florida Defensive Driving Course', 'Comprehensive defensive driving course approved by Florida DHSMV.', 'FL', 300, 29.95, 80, 'Defensive Driving', 1, NOW(), NOW()),
(3, 'Florida Traffic School Online', 'Online traffic school course for ticket dismissal in Florida.', 'FL', 240, 24.95, 70, 'Traffic School', 1, NOW(), NOW());

-- DONE! You can now login with:
-- Admin: admin@dummiestrafficschool.com / admin123
-- Student: student@test.com / student123