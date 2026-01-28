-- Create Test User for UI/UX Testing
-- Run this in your MySQL database: nelly-elearning

-- Create test user for Florida (main test user)
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
    '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: password
    'florida',
    NOW(),
    NOW(),
    NOW()
) ON DUPLICATE KEY UPDATE 
    name = VALUES(name),
    state_code = VALUES(state_code);

-- Create test users for other states
INSERT INTO users (
    name, 
    email, 
    password, 
    state_code, 
    email_verified_at, 
    created_at, 
    updated_at
) VALUES 
(
    'Missouri Test User',
    'missouri@example.com',
    '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: password
    'missouri',
    NOW(),
    NOW(),
    NOW()
),
(
    'Texas Test User',
    'texas@example.com',
    '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: password
    'texas',
    NOW(),
    NOW(),
    NOW()
),
(
    'Delaware Test User',
    'delaware@example.com',
    '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: password
    'delaware',
    NOW(),
    NOW(),
    NOW()
) ON DUPLICATE KEY UPDATE 
    name = VALUES(name),
    state_code = VALUES(state_code);

-- Verify users were created
SELECT id, name, email, state_code, created_at FROM users WHERE email LIKE '%@example.com';