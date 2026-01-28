-- Clean SQL commands to fix role issues
-- Run these one by one in your MySQL database

-- Step 1: Create default roles if they don't exist
INSERT INTO roles (name, slug, created_at, updated_at) VALUES 
('Student', 'student', NOW(), NOW()),
('Admin', 'admin', NOW(), NOW()),
('Super Admin', 'super-admin', NOW(), NOW())
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- Step 2: Update test user to have admin role
UPDATE users SET role_id = (SELECT id FROM roles WHERE slug = 'admin' LIMIT 1) 
WHERE email = 'test@example.com';

-- Step 3: Assign student role to any users without roles
UPDATE users SET role_id = (SELECT id FROM roles WHERE slug = 'student' LIMIT 1) 
WHERE role_id IS NULL;

-- Step 4: Verify the fix
SELECT u.name, u.email, r.name as role_name, r.slug as role_slug 
FROM users u 
LEFT JOIN roles r ON u.role_id = r.id 
WHERE u.email = 'test@example.com';