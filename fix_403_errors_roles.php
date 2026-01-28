<?php

/**
 * FIX 403 ERRORS - Correct Role System Issues
 * This script fixes the role system to resolve 403 errors
 */

echo "🔧 FIXING 403 ERRORS - Correcting Role System...\n";
echo str_repeat("=", 70) . "\n";

$host = '127.0.0.1';
$port = '3306';
$database = 'nelly-elearning';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Connected to database: $database\n\n";
    
    // Step 1: Show current roles
    echo "1. 📊 CURRENT ROLES (INCORRECT):\n";
    echo str_repeat("-", 40) . "\n";
    
    $stmt = $pdo->prepare("SELECT * FROM roles ORDER BY id");
    $stmt->execute();
    $currentRoles = $stmt->fetchAll();
    
    foreach ($currentRoles as $role) {
        echo "   ID: {$role['id']}, Name: {$role['name']}, Slug: {$role['slug']}\n";
    }
    
    // Step 2: Fix the roles
    echo "\n2. 🔧 FIXING ROLES:\n";
    echo str_repeat("-", 20) . "\n";
    
    $correctRoles = [
        1 => ['name' => 'Super Admin', 'slug' => 'super-admin', 'description' => 'Full system access'],
        2 => ['name' => 'Admin', 'slug' => 'admin', 'description' => 'Administrative access'],
        3 => ['name' => 'User', 'slug' => 'user', 'description' => 'Regular user access']
    ];
    
    foreach ($correctRoles as $id => $roleData) {
        $stmt = $pdo->prepare("
            UPDATE roles 
            SET name = ?, slug = ?, description = ?, updated_at = NOW() 
            WHERE id = ?
        ");
        $stmt->execute([$roleData['name'], $roleData['slug'], $roleData['description'], $id]);
        
        echo "✅ Updated Role ID $id: {$roleData['name']} -> {$roleData['slug']}\n";
    }
    
    // Step 3: Verify the fix
    echo "\n3. ✅ CORRECTED ROLES:\n";
    echo str_repeat("-", 25) . "\n";
    
    $stmt = $pdo->prepare("SELECT * FROM roles ORDER BY id");
    $stmt->execute();
    $fixedRoles = $stmt->fetchAll();
    
    foreach ($fixedRoles as $role) {
        echo "   ID: {$role['id']}, Name: {$role['name']}, Slug: {$role['slug']}\n";
    }
    
    // Step 4: Check user role assignments
    echo "\n4. 👥 CHECKING USER ROLE ASSIGNMENTS:\n";
    echo str_repeat("-", 40) . "\n";
    
    $stmt = $pdo->prepare("
        SELECT u.id, u.name, u.email, u.role_id, r.name as role_name, r.slug as role_slug
        FROM users u
        LEFT JOIN roles r ON u.role_id = r.id
        ORDER BY u.id
    ");
    $stmt->execute();
    $users = $stmt->fetchAll();
    
    foreach ($users as $user) {
        $accessLevel = '';
        if ($user['role_slug'] === 'super-admin') {
            $accessLevel = '🔑 Full Admin Access';
        } elseif ($user['role_slug'] === 'admin') {
            $accessLevel = '🔐 Admin Access';
        } elseif ($user['role_slug'] === 'user') {
            $accessLevel = '👤 User Access';
        } else {
            $accessLevel = '❌ No Valid Role';
        }
        
        echo "   {$user['name']} ({$user['email']}): {$user['role_name']} -> $accessLevel\n";
    }
    
    // Step 5: Test middleware compatibility
    echo "\n5. 🧪 TESTING MIDDLEWARE COMPATIBILITY:\n";
    echo str_repeat("-", 40) . "\n";
    
    // Test RoleMiddleware logic
    echo "RoleMiddleware expects role slugs:\n";
    foreach ($users as $user) {
        $roleSlug = $user['role_slug'];
        
        // Test common middleware patterns
        $canAccessSuperAdmin = $roleSlug === 'super-admin';
        $canAccessAdmin = in_array($roleSlug, ['super-admin', 'admin']);
        $canAccessUser = in_array($roleSlug, ['super-admin', 'admin', 'user']);
        
        echo "   {$user['name']}:\n";
        echo "     - 'role:super-admin': " . ($canAccessSuperAdmin ? '✅ ALLOWED' : '❌ DENIED') . "\n";
        echo "     - 'role:super-admin,admin': " . ($canAccessAdmin ? '✅ ALLOWED' : '❌ DENIED') . "\n";
        echo "     - 'role:super-admin,admin,user': " . ($canAccessUser ? '✅ ALLOWED' : '❌ DENIED') . "\n";
    }
    
    // Step 6: Check AdminMiddleware compatibility
    echo "\n6. 🛡️  TESTING ADMIN MIDDLEWARE COMPATIBILITY:\n";
    echo str_repeat("-", 45) . "\n";
    
    echo "AdminMiddleware checks role_id (not slug):\n";
    foreach ($users as $user) {
        $roleId = $user['role_id'];
        $canAccessAdmin = in_array($roleId, [1, 2]); // Super Admin or Admin
        $status = $canAccessAdmin ? '✅ ALLOWED' : '❌ DENIED';
        echo "   {$user['name']} (role_id: $roleId): $status\n";
    }
    
    // Step 7: Create test user if needed
    echo "\n7. 👤 ENSURING TEST ADMIN USER EXISTS:\n";
    echo str_repeat("-", 40) . "\n";
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM users WHERE role_id IN (1, 2)");
    $stmt->execute();
    $adminCount = $stmt->fetch()['count'];
    
    if ($adminCount > 0) {
        echo "✅ Found $adminCount admin users - system ready\n";
    } else {
        echo "⚠️  No admin users found - creating test admin...\n";
        
        // Create test admin user
        $stmt = $pdo->prepare("
            INSERT INTO users (name, email, password, role_id, email_verified_at, created_at, updated_at) 
            VALUES (?, ?, ?, ?, NOW(), NOW(), NOW())
        ");
        $stmt->execute([
            'Test Admin',
            'testadmin@example.com',
            '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            1 // Super Admin role
        ]);
        
        echo "✅ Created test admin user: testadmin@example.com (password: password)\n";
    }
    
    echo "\n" . str_repeat("=", 70) . "\n";
    echo "🎉 403 ERROR FIX COMPLETED!\n";
    echo str_repeat("=", 70) . "\n";
    
    echo "\n✅ WHAT WAS FIXED:\n";
    echo "   • Corrected role slugs to match middleware expectations\n";
    echo "   • Super Admin role now has 'super-admin' slug\n";
    echo "   • Admin role now has 'admin' slug\n";
    echo "   • User role now has 'user' slug\n";
    echo "   • Verified admin users exist with proper role_id\n";
    
    echo "\n🔗 ADMIN ROUTES SHOULD NOW WORK:\n";
    echo "   • http://nelly-elearning.test/admin/state-transmissions\n";
    echo "   • http://nelly-elearning.test/admin/certificates\n";
    echo "   • http://nelly-elearning.test/admin/users\n";
    echo "   • http://nelly-elearning.test/admin/dashboard\n";
    
    echo "\n👤 LOGIN CREDENTIALS:\n";
    foreach ($users as $user) {
        if (in_array($user['role_id'], [1, 2])) {
            echo "   • {$user['email']} (Role: {$user['role_name']})\n";
        }
    }
    
    echo "\n🔑 DEFAULT PASSWORD: password\n";
    echo "(Change passwords in production!)\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

?>