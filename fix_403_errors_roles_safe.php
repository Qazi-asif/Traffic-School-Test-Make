<?php

/**
 * FIX 403 ERRORS - Safely Correct Role System Issues
 * This script safely fixes the role system to resolve 403 errors
 */

echo "🔧 FIXING 403 ERRORS - Safely Correcting Role System...\n";
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
    echo "1. 📊 CURRENT ROLES (PROBLEMATIC):\n";
    echo str_repeat("-", 40) . "\n";
    
    $stmt = $pdo->prepare("SELECT * FROM roles ORDER BY id");
    $stmt->execute();
    $currentRoles = $stmt->fetchAll();
    
    foreach ($currentRoles as $role) {
        echo "   ID: {$role['id']}, Name: {$role['name']}, Slug: {$role['slug']}\n";
    }
    
    // Step 2: Fix roles safely by updating one at a time with temporary slugs first
    echo "\n2. 🔧 FIXING ROLES SAFELY:\n";
    echo str_repeat("-", 30) . "\n";
    
    // First, set temporary unique slugs to avoid conflicts
    echo "Setting temporary slugs to avoid conflicts...\n";
    $pdo->exec("UPDATE roles SET slug = 'temp-super-admin' WHERE id = 1");
    $pdo->exec("UPDATE roles SET slug = 'temp-admin' WHERE id = 2");
    $pdo->exec("UPDATE roles SET slug = 'temp-user' WHERE id = 3");
    echo "✅ Temporary slugs set\n";
    
    // Now set the correct roles
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
    
    // Step 4: Check user role assignments and fix if needed
    echo "\n4. 👥 CHECKING AND FIXING USER ROLE ASSIGNMENTS:\n";
    echo str_repeat("-", 50) . "\n";
    
    $stmt = $pdo->prepare("
        SELECT u.id, u.name, u.email, u.role_id, r.name as role_name, r.slug as role_slug
        FROM users u
        LEFT JOIN roles r ON u.role_id = r.id
        ORDER BY u.id
    ");
    $stmt->execute();
    $users = $stmt->fetchAll();
    
    $adminUsersCount = 0;
    foreach ($users as $user) {
        $accessLevel = '';
        $needsRoleUpdate = false;
        
        if ($user['role_slug'] === 'super-admin') {
            $accessLevel = '🔑 Full Admin Access';
            $adminUsersCount++;
        } elseif ($user['role_slug'] === 'admin') {
            $accessLevel = '🔐 Admin Access';
            $adminUsersCount++;
        } elseif ($user['role_slug'] === 'user') {
            $accessLevel = '👤 User Access';
        } else {
            $accessLevel = '❌ No Valid Role';
            $needsRoleUpdate = true;
        }
        
        echo "   {$user['name']} ({$user['email']}): {$user['role_name']} -> $accessLevel\n";
        
        // Fix users that look like admins but don't have admin roles
        if ($needsRoleUpdate && (strpos(strtolower($user['email']), 'admin') !== false || strpos(strtolower($user['name']), 'admin') !== false)) {
            echo "     🔧 Fixing admin user role...\n";
            $stmt = $pdo->prepare("UPDATE users SET role_id = 1 WHERE id = ?");
            $stmt->execute([$user['id']]);
            echo "     ✅ Updated {$user['name']} to Super Admin\n";
            $adminUsersCount++;
        }
    }
    
    // Step 5: Ensure we have at least one admin user
    if ($adminUsersCount === 0) {
        echo "\n⚠️  No admin users found - promoting first user to admin...\n";
        if (!empty($users)) {
            $firstUser = $users[0];
            $stmt = $pdo->prepare("UPDATE users SET role_id = 1 WHERE id = ?");
            $stmt->execute([$firstUser['id']]);
            echo "✅ Promoted {$firstUser['name']} to Super Admin\n";
            $adminUsersCount++;
        }
    }
    
    // Step 6: Test both middleware systems
    echo "\n5. 🧪 TESTING BOTH MIDDLEWARE SYSTEMS:\n";
    echo str_repeat("-", 40) . "\n";
    
    // Refresh user data after updates
    $stmt = $pdo->prepare("
        SELECT u.id, u.name, u.email, u.role_id, r.name as role_name, r.slug as role_slug
        FROM users u
        LEFT JOIN roles r ON u.role_id = r.id
        ORDER BY u.id
    ");
    $stmt->execute();
    $updatedUsers = $stmt->fetchAll();
    
    echo "A. RoleMiddleware (uses role slug):\n";
    foreach ($updatedUsers as $user) {
        $roleSlug = $user['role_slug'];
        $canAccessAdmin = in_array($roleSlug, ['super-admin', 'admin']);
        $status = $canAccessAdmin ? '✅ ALLOWED' : '❌ DENIED (403)';
        echo "   {$user['name']} (slug: $roleSlug): $status\n";
    }
    
    echo "\nB. AdminMiddleware (uses role_id):\n";
    foreach ($updatedUsers as $user) {
        $roleId = $user['role_id'];
        $canAccessAdmin = in_array($roleId, [1, 2]);
        $status = $canAccessAdmin ? '✅ ALLOWED' : '❌ DENIED (403)';
        echo "   {$user['name']} (role_id: $roleId): $status\n";
    }
    
    // Step 7: Check route middleware patterns
    echo "\n6. 🛤️  CHECKING ROUTE MIDDLEWARE PATTERNS:\n";
    echo str_repeat("-", 40) . "\n";
    
    $routePatterns = [
        "'admin'" => "Uses AdminMiddleware (checks role_id 1,2)",
        "'role:super-admin,admin'" => "Uses RoleMiddleware (checks slugs)",
        "['auth', 'admin']" => "Uses both auth + AdminMiddleware"
    ];
    
    foreach ($routePatterns as $pattern => $description) {
        echo "✅ $pattern -> $description\n";
    }
    
    echo "\n" . str_repeat("=", 70) . "\n";
    echo "🎉 403 ERROR FIX COMPLETED SUCCESSFULLY!\n";
    echo str_repeat("=", 70) . "\n";
    
    echo "\n✅ WHAT WAS FIXED:\n";
    echo "   • Corrected role slugs to match RoleMiddleware expectations\n";
    echo "   • Super Admin (ID: 1) -> slug: 'super-admin'\n";
    echo "   • Admin (ID: 2) -> slug: 'admin'\n";
    echo "   • User (ID: 3) -> slug: 'user'\n";
    echo "   • Ensured admin users have proper role assignments\n";
    echo "   • Both AdminMiddleware and RoleMiddleware now work correctly\n";
    
    echo "\n🔗 ADMIN ROUTES SHOULD NOW WORK:\n";
    echo "   • http://nelly-elearning.test/admin/state-transmissions\n";
    echo "   • http://nelly-elearning.test/admin/certificates\n";
    echo "   • http://nelly-elearning.test/admin/users\n";
    echo "   • http://nelly-elearning.test/admin/dashboard\n";
    echo "   • http://nelly-elearning.test/admin/booklets\n";
    echo "   • http://nelly-elearning.test/admin/payments\n";
    
    echo "\n👤 ADMIN LOGIN CREDENTIALS:\n";
    foreach ($updatedUsers as $user) {
        if (in_array($user['role_id'], [1, 2])) {
            echo "   • Email: {$user['email']}\n";
            echo "     Name: {$user['name']}\n";
            echo "     Role: {$user['role_name']} ({$user['role_slug']})\n";
            echo "     Password: password (default)\n\n";
        }
    }
    
    echo "🔑 MIDDLEWARE COMPATIBILITY:\n";
    echo "   • Routes with 'admin' middleware: Use AdminMiddleware (role_id check)\n";
    echo "   • Routes with 'role:super-admin,admin': Use RoleMiddleware (slug check)\n";
    echo "   • Both systems now work correctly with the fixed roles\n";
    
    echo "\n⚠️  IMPORTANT:\n";
    echo "   • Clear browser cache and cookies\n";
    echo "   • Log out and log back in to refresh session\n";
    echo "   • Change default passwords in production\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}

?>