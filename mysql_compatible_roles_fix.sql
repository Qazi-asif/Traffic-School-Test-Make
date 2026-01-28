-- MySQL compatible commands to create roles table and fix user roles
-- Run these commands one by one in your MySQL database

-- Step 1: Create the roles table
CREATE TABLE IF NOT EXISTS `roles` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `slug` varchar(255) NOT NULL UNIQUE,
    `description` text NULL,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `roles_slug_index` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Step 2: Insert default roles
INSERT INTO roles (name, slug, description, created_at, updated_at) VALUES 
('Student', 'student', 'Regular student user', NOW(), NOW()),
('Admin', 'admin', 'Administrator user', NOW(), NOW()),
('Super Admin', 'super-admin', 'Super administrator with full access', NOW(), NOW());

-- Step 3: Check if role_id column exists, if not add it
-- (Run this to check first)
SELECT COLUMN_NAME 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'nelly-elearning' 
AND TABLE_NAME = 'users' 
AND COLUMN_NAME = 'role_id';

-- Step 4: Add role_id column (only run if the above query returns no results)
ALTER TABLE users ADD COLUMN `role_id` bigint(20) unsigned NULL;

-- Step 5: Update test user to have admin role
UPDATE users SET role_id = (SELECT id FROM roles WHERE slug = 'admin' LIMIT 1) 
WHERE email = 'test@example.com';

-- Step 6: Assign student role to any users without roles
UPDATE users SET role_id = (SELECT id FROM roles WHERE slug = 'student' LIMIT 1) 
WHERE role_id IS NULL;

-- Step 7: Verify everything is working
SELECT u.name, u.email, r.name as role_name, r.slug as role_slug 
FROM users u 
LEFT JOIN roles r ON u.role_id = r.id 
WHERE u.email = 'test@example.com';