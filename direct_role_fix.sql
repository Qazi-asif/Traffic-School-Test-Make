-- Direct SQL fix for role system to resolve 403 errors
-- This fixes the role slugs to match middleware expectations

-- First, set temporary slugs to avoid unique constraint conflicts
UPDATE roles SET slug = 'temp-super-admin' WHERE id = 1;
UPDATE roles SET slug = 'temp-admin' WHERE id = 2;
UPDATE roles SET slug = 'temp-user' WHERE id = 3;

-- Now set the correct roles
UPDATE roles SET 
    name = 'Super Admin', 
    slug = 'super-admin', 
    description = 'Full system access',
    updated_at = NOW()
WHERE id = 1;

UPDATE roles SET 
    name = 'Admin', 
    slug = 'admin', 
    description = 'Administrative access',
    updated_at = NOW()
WHERE id = 2;

UPDATE roles SET 
    name = 'User', 
    slug = 'user', 
    description = 'Regular user access',
    updated_at = NOW()
WHERE id = 3;

-- Ensure we have at least one admin user
-- Update first user to be super admin if no admin exists
UPDATE users SET role_id = 1 
WHERE id = (SELECT MIN(id) FROM users) 
AND NOT EXISTS (SELECT 1 FROM users WHERE role_id IN (1, 2));

-- Show final results
SELECT 'ROLES AFTER FIX:' as status;
SELECT id, name, slug, description FROM roles ORDER BY id;

SELECT 'ADMIN USERS:' as status;
SELECT u.id, u.name, u.email, u.role_id, r.name as role_name, r.slug as role_slug
FROM users u
LEFT JOIN roles r ON u.role_id = r.id
WHERE u.role_id IN (1, 2)
ORDER BY u.id;