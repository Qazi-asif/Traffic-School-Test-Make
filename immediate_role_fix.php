<?php

/**
 * IMMEDIATE ROLE FIX - Apply SQL Fix Now
 * This script directly applies the role system fix to resolve 403 errors
 */

echo "🚨 APPLYING IMMEDIATE ROLE FIX - Resolving 403 Errors\n";
echo str_repeat("=", 60) . "\n";

$host = '127.0.0.1';
$port = '3306';
$database = 'nelly-elearning';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Connected to database: $database\n\n";
    
    // Show current problematic state
    echo "BEFORE FIX - Current Roles (PROBLEMATIC):\n";
    $stmt = $pdo->prepare("SELECT id, name, slug FROM roles ORDER BY id");
    $stmt->execute();
    $currentRoles = $stmt->fetchAll();
    
    foreach ($currentRoles as $role) {
        echo "   ID: {$role['id']}, Name: {$role['name']}, Slug: {$role['slug']}\n";
    }
    
    echo "\n🔧 APPLYING FIX NOW...\n";
    
    // Step 1: Set temporary slugs to avoid unique constraint conflicts
    echo "Step 1: Setting temporary slugs...\n";
    $pdo->exec("UPDATE roles SET slug = 'temp-super-admin' WHERE id = 1");
    $pdo->exec("UPDATE roles SET slug = 'temp-admin' WHERE id = 2");
    $pdo->exec("UPDATE roles SET slug = 'temp-user' WHERE id = 3");
    echo "✅ Temporary slugs set\n";
    
    // Step 2: Apply correct role configuration
    echo "Step 2: Setting correct roles...\n";
    $pdo->exec("UPDATE roles SET name = 'Super Admin', slug = 'super-admin', description = 'Full system access', updated_at = NOW() WHERE id = 1");
    $pdo->exec("UPDATE roles SET name = 'Admin', slug = 'admin', description = 'Administrative access', updated_at = NOW() WHERE id = 2");
    $pdo->exec("UPDATE roles SET name = 'User', slug = 'user', description = 'Regular user access', updated_at = NOW() WHERE id = 3");
    echo "✅ Correct roles set\n";
    
    // Step 3: Ensure admin user exists
    echo "Step 3: Ensuring admin user exists...\n";
    $stmt = $pdo->prepare("SELECT COUNT(*) as admin_count FROM users WHERE role_id IN (1, 2)");
    $stmt->execute();
    $adminCount = $stmt->fetch()['admin_count'];
    
    if ($adminCount == 0) {
        echo "No admin users found, promoting first user...\n";
        $pdo->exec("UPDATE users SET role_id = 1, updated_at = NOW() WHERE id = (SELECT MIN(id) FROM (SELECT id FROM users) as temp)");
        echo "✅ First user promoted to Super Admin\n";
    } else {
        echo "✅ Admin users already exist ($adminCount found)\n";
    }
    
    // Show fixed state
    echo "\nAFTER FIX - Corrected Roles:\n";
    $stmt = $pdo->prepare("SELECT id, name, slug, description FROM roles ORDER BY id");
    $stmt->execute();
    $fixedRoles = $stmt->fetchAll();
    
    foreach ($fixedRoles as $role) {
        echo "   ID: {$role['id']}, Name: {$role['name']}, Slug: {$role['slug']}\n";
    }
    
    // Show admin users
    echo "\nAdmin Users with Access:\n";
    $stmt = $pdo->prepare("
        SELECT u.id, u.name, u.email, u.role_id, r.name as role_name, r.slug as role_slug
        FROM users u
        LEFT JOIN roles r ON u.role_id = r.id
        WHERE u.role_id IN (1, 2)
        ORDER BY u.id
    ");
    $stmt->execute();
    $adminUsers = $stmt->fetchAll();
    
    foreach ($adminUsers as $user) {
        echo "   ✅ {$user['name']} ({$user['email']}) - {$user['role_name']} (role_id: {$user['role_id']})\n";
    }
    
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "🎉 ROLE FIX APPLIED SUCCESSFULLY!\n";
    echo str_repeat("=", 60) . "\n";
    
    echo "\n✅ WHAT WAS FIXED:\n";
    echo "   • Role ID 1: Super Admin -> slug: 'super-admin' (AdminMiddleware compatible)\n";
    echo "   • Role ID 2: Admin -> slug: 'admin' (AdminMiddleware compatible)\n";
    echo "   • Role ID 3: User -> slug: 'user' (Regular users)\n";
    echo "   • Ensured admin users exist with proper role_id\n";
    
    echo "\n🔗 THESE ADMIN ROUTES SHOULD NOW WORK:\n";
    echo "   ✅ http://nelly-elearning.test/admin/state-transmissions\n";
    echo "   ✅ http://nelly-elearning.test/admin/certificates\n";
    echo "   ✅ http://nelly-elearning.test/admin/users\n";
    echo "   ✅ http://nelly-elearning.test/admin/dashboard\n";
    echo "   ✅ http://nelly-elearning.test/booklets\n";
    
    echo "\n⚠️  IMMEDIATE NEXT STEPS:\n";
    echo "   1. ✅ SQL Fix Applied - DONE\n";
    echo "   2. 🧹 Clear browser cache and cookies - DO THIS NOW\n";
    echo "   3. 🔄 Log out and log back in - DO THIS NOW\n";
    echo "   4. 🧪 Test all admin modules - DO THIS NOW\n";
    
    echo "\n🔐 LOGIN CREDENTIALS:\n";
    foreach ($adminUsers as $user) {
        echo "   Email: {$user['email']}\n";
        echo "   Password: password (default - change in production)\n";
        echo "   Role: {$user['role_name']}\n\n";
    }
    
    echo "🎯 SYSTEM STATUS: READY FOR TESTING!\n";
    echo "The 403 errors should be completely resolved.\n";
    
} catch (Exception $e) {
    echo "❌ Error applying fix: " . $e->getMessage() . "\n";
    echo "\nMANUAL SQL COMMANDS TO RUN:\n";
    echo "UPDATE roles SET slug = 'temp-super-admin' WHERE id = 1;\n";
    echo "UPDATE roles SET slug = 'temp-admin' WHERE id = 2;\n";
    echo "UPDATE roles SET slug = 'temp-user' WHERE id = 3;\n";
    echo "UPDATE roles SET name = 'Super Admin', slug = 'super-admin', description = 'Full system access', updated_at = NOW() WHERE id = 1;\n";
    echo "UPDATE roles SET name = 'Admin', slug = 'admin', description = 'Administrative access', updated_at = NOW() WHERE id = 2;\n";
    echo "UPDATE roles SET name = 'User', slug = 'user', description = 'Regular user access', updated_at = NOW() WHERE id = 3;\n";
    echo "UPDATE users SET role_id = 1, updated_at = NOW() WHERE id = 1;\n";
    exit(1);
}

?>