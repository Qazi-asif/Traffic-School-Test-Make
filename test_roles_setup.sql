-- Quick test to verify roles are set up correctly
-- Run this to check your current setup

-- Check if roles table exists and has data
SELECT 'Roles in database:' as info;
SELECT * FROM roles;

-- Check test user role assignment
SELECT 'Test user role assignment:' as info;
SELECT u.name, u.email, u.role_id, r.name as role_name, r.slug as role_slug 
FROM users u 
LEFT JOIN roles r ON u.role_id = r.id 
WHERE u.email = 'test@example.com';

-- Check all users and their roles
SELECT 'All users and roles:' as info;
SELECT u.name, u.email, r.name as role_name, r.slug as role_slug 
FROM users u 
LEFT JOIN roles r ON u.role_id = r.id 
ORDER BY u.id;

-- Count users by role
SELECT 'Users by role:' as info;
SELECT r.name as role_name, COUNT(u.id) as user_count
FROM roles r
LEFT JOIN users u ON r.id = u.role_id
GROUP BY r.id, r.name;