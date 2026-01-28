<?php

/**
 * Fix User Role Issue
 * 
 * This script ensures the test user has a proper role assigned
 * and fixes any role-related database issues.
 */

echo "🔧 FIXING USER ROLE ISSUE\n";
echo str_repeat("=", 40) . "\n\n";

try {
    // Connect to database
    $pdo = new PDO(
        "mysql:host=127.0.0.1;dbname=nelly-elearning", 
        "root", 
        "", 
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "✅ Database connection successful\n\n";
    
    // Step 1: Check if roles table exists and has data
    echo "📊 CHECKING ROLES TABLE:\n";
    echo str_repeat("-", 25) . "\n";
    
    $rolesCount = $pdo->query("SELECT COUNT(*) FROM roles")->fetchColumn();
    echo "• Roles table records: {$rolesCount}\n";
    
    if ($rolesCount == 0) {
        echo "🔧 Creating default roles...\n";
        
        // Create default roles
        $pdo->exec("
            INSERT INTO roles (name, slug, created_at, updated_at) VALUES 
            ('Student', 'student', NOW(), NOW()),
            ('Admin', 'admin', NOW(), NOW()),
            ('Super Admin', 'super-admin', NOW(), NOW())
            ON DUPLICATE KEY UPDATE name = VALUES(name)
        ");
        
        echo "✅ Default roles created\n";
    }
    
    // Step 2: Get role IDs
    $studentRoleId = $pdo->query("SELECT id FROM roles WHERE slug = 'student' LIMIT 1")->fetchColumn();
    $adminRoleId = $pdo->query("SELECT id FROM roles WHERE slug = 'admin' LIMIT 1")->fetchColumn();
    
    echo "• Student role ID: {$studentRoleId}\n";
    echo "• Admin role ID: {$adminRoleId}\n";
    
    // Step 3: Check test user
    echo "\n📊 CHECKING TEST USER:\n";
    echo str_repeat("-", 25) . "\n";
    
    $testUser = $pdo->query("SELECT * FROM users WHERE email = 'test@example.com' LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    
    if ($testUser) {
        echo "• Test user exists: ID {$testUser['id']}\n";
        echo "• Current role_id: " . ($testUser['role_id'] ?? 'NULL') . "\n";
        
        // Update test user to have admin role
        if (!$testUser['role_id'] || $testUser['role_id'] != $adminRoleId) {
            $pdo->prepare("UPDATE users SET role_id = ? WHERE email = 'test@example.com'")
                ->execute([$adminRoleId]);
            echo "✅ Updated test user to admin role\n";
        } else {
            echo "✅ Test user already has correct role\n";
        }
    } else {
        echo "🔧 Creating test user with admin role...\n";
        
        $pdo->prepare("
            INSERT INTO users (
                name, email, password, role_id, state_code, 
                email_verified_at, created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, NOW(), NOW(), NOW())
        ")->execute([
            'Test User',
            'test@example.com',
            '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password: 'password'
            $adminRoleId,
            'florida'
        ]);
        
        echo "✅ Test user created with admin role\n";
    }
    
    // Step 4: Check all users have roles
    echo "\n📊 CHECKING ALL USERS:\n";
    echo str_repeat("-", 25) . "\n";
    
    $usersWithoutRoles = $pdo->query("SELECT COUNT(*) FROM users WHERE role_id IS NULL")->fetchColumn();
    echo "• Users without roles: {$usersWithoutRoles}\n";
    
    if ($usersWithoutRoles > 0) {
        echo "🔧 Assigning student role to users without roles...\n";
        
        $pdo->prepare("UPDATE users SET role_id = ? WHERE role_id IS NULL")
            ->execute([$studentRoleId]);
            
        echo "✅ Assigned student role to {$usersWithoutRoles} users\n";
    }
    
    // Step 5: Verification
    echo "\n📊 FINAL VERIFICATION:\n";
    echo str_repeat("-", 25) . "\n";
    
    $finalTestUser = $pdo->query("
        SELECT u.*, r.name as role_name, r.slug as role_slug 
        FROM users u 
        LEFT JOIN roles r ON u.role_id = r.id 
        WHERE u.email = 'test@example.com' 
        LIMIT 1
    ")->fetch(PDO::FETCH_ASSOC);
    
    if ($finalTestUser) {
        echo "• Test user: {$finalTestUser['name']}\n";
        echo "• Email: {$finalTestUser['email']}\n";
        echo "• Role: {$finalTestUser['role_name']} ({$finalTestUser['role_slug']})\n";
        echo "• State: {$finalTestUser['state_code']}\n";
    }
    
    $totalUsersWithRoles = $pdo->query("
        SELECT COUNT(*) FROM users u 
        INNER JOIN roles r ON u.role_id = r.id
    ")->fetchColumn();
    
    echo "• Total users with valid roles: {$totalUsersWithRoles}\n";
    
    echo "\n" . str_repeat("=", 40) . "\n";
    echo "✅ USER ROLE ISSUE FIXED!\n";
    echo str_repeat("=", 40) . "\n\n";
    
    echo "🎯 WHAT WAS FIXED:\n";
    echo "• Ensured roles table has default roles\n";
    echo "• Test user now has admin role\n";
    echo "• All users have valid role assignments\n";
    echo "• Role relationships are properly configured\n\n";
    
    echo "✅ YOU CAN NOW LOGIN WITHOUT ROLE ERRORS!\n";
    echo "URL: http://nelly-elearning.test/emergency-login\n\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Please check your database connection and try again.\n\n";
}

?>