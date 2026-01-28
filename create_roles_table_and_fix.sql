-- Create roles table and fix user role issues
-- Run these commands in order in your MySQL database

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

-- Step 3: Add role_id column to users table if it doesn't exist
ALTER TABLE users ADD COLUMN IF NOT EXISTS `role_id` bigint(20) unsigned NULL;

-- Step 4: Add foreign key constraint
ALTER TABLE users ADD CONSTRAINT `users_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE SET NULL;

-- Step 5: Update test user to have admin role
UPDATE users SET role_id = (SELECT id FROM roles WHERE slug = 'admin' LIMIT 1) 
WHERE email = 'test@example.com';

-- Step 6: Assign student role to any users without roles
UPDATE users SET role_id = (SELECT id FROM roles WHERE slug = 'student' LIMIT 1) 
WHERE role_id IS NULL;

-- Step 7: Verify the setup
SELECT 'Roles created:' as info;
SELECT * FROM roles;

SELECT 'Test user role:' as info;
SELECT u.name, u.email, r.name as role_name, r.slug as role_slug 
FROM users u 
LEFT JOIN roles r ON u.role_id = r.id 
WHERE u.email = 'test@example.com';

SELECT 'Users without roles:' as info;
SELECT COUNT(*) as users_without_roles FROM users WHERE role_id IS NULL;