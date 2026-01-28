-- Fix Missing login_attempts Table
-- Run this in your MySQL database: nelly-elearning

-- Create login_attempts table
CREATE TABLE IF NOT EXISTS `login_attempts` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `email` varchar(255) NOT NULL,
    `ip_address` varchar(45) NOT NULL,
    `user_agent` text,
    `successful` tinyint(1) NOT NULL DEFAULT 0,
    `attempted_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `login_attempts_email_index` (`email`),
    KEY `login_attempts_ip_address_index` (`ip_address`),
    KEY `login_attempts_attempted_at_index` (`attempted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create test user (password is 'password')
INSERT INTO users (
    name, 
    email, 
    password, 
    state_code, 
    email_verified_at, 
    created_at, 
    updated_at
) VALUES (
    'Test User',
    'test@example.com',
    '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'florida',
    NOW(),
    NOW(),
    NOW()
) ON DUPLICATE KEY UPDATE 
    name = VALUES(name),
    state_code = VALUES(state_code);

-- Verify tables exist
SELECT 'login_attempts table' as table_name, COUNT(*) as exists_check 
FROM information_schema.tables 
WHERE table_schema = 'nelly-elearning' AND table_name = 'login_attempts'
UNION ALL
SELECT 'users table' as table_name, COUNT(*) as exists_check 
FROM information_schema.tables 
WHERE table_schema = 'nelly-elearning' AND table_name = 'users';

-- Show test user
SELECT id, name, email, state_code, created_at FROM users WHERE email = 'test@example.com';