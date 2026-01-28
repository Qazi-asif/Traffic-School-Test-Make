<?php

/**
 * Execute Role Fix - Direct Database Update
 * This script directly fixes the role system to resolve 403 errors
 */

echo "🔧 EXECUTING ROLE FIX - Direct Database Update...\n";
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
    echo "1. 📊 CURRENT ROLES (PROBLEMATIC):\n";
    echo str_repeat("-", 40) . "\n";
    
    $stmt = $pdo->prepare("SELECT * FROM roles ORDER BY id");
    $stmt->execute();
    $currentRoles = $stmt->fetchAll();
    
    foreach ($currentRoles as $role) {
        echo "   ID: {$role['id']}, Name: {$role['name']}, Slug: {$role['slug']}\n";
    }
    
    // Execute the fix
    echo "\n2. 🔧 EXECUTING FIX:\n";
    echo str_repeat("-", 20) . "\n";
    
    // Step 1: Set temporary slugs to avoid conflicts
    echo "Setting temporary slugs...\n";
    $pdo->exec("UPDATE roles SET slug = 'temp-super-admin' WHERE id = 1");
    $pdo->exec("UPDATE roles SET slug = 'temp-admin' WHERE id = 2");
    $pdo->exec("UPDATE roles SET slug = 'temp-user' WHERE id = 3");
    echo "✅ Temporary slugs set\n";
    
    // Step 2: Set correct roles
    echo "Setting correct roles...\n";
    $pdo->exec("UPDATE roles SET name = 'Super Admin', slug = 'super-admin', description = 'Full system access', updated_at = NOW() WHERE id = 1");
    $pdo->exec("UPDATE roles SET name = 'Admin', slug = 'admin', description = 'Administrative access', updated_at = NOW() WHERE id = 2");
    $pdo->exec("UPDATE roles SET name = 'User', slug = 'user', description = 'Regular user access', updated_at = NOW() WHERE id = 3");
    echo "✅ Correct roles set\n";
    
    // Step 3: Ensure admin user exists
    echo "Ensuring admin user exists...\n";
    $stmt = $pdo->prepare("SELECT COUNT(*) as admin_count FROM users WHERE role_id IN (1, 2)");
    $stmt->execute();
    $adminCount = $stmt->fetch()['admin_count'];
    
    if ($adminCount == 0) {
        echo "No admin users found, promoting first user...\n";
        $pdo->exec("UPDATE users SET role_id = 1 WHERE id = (SELECT MIN(id) FROM (SELECT id FROM users) as temp)");
        echo "✅ First user promoted to Super Admin\n";
    } else {
        echo "✅ Admin users already exist ($adminCount found)\n";
    }
    
    // Step 4: Show results
    echo "\n3. ✅ FIXED ROLES:\n";
    echo str_repeat("-", 20) . "\n";
    
    $stmt = $pdo->prepare("SELECT * FROM roles ORDER BY id");
    $stmt->execute();
    $fixedRoles = $stmt->fetchAll();
    
    foreach ($fixedRoles as $role) {
        echo "   ID: {$role['id']}, Name: {$role['name']}, Slug: {$role['slug']}\n";
    }
    
    // Step 5: Show admin users
    echo "\n4. 👥 ADMIN USERS:\n";
    echo str_repeat("-", 15) . "\n";
    
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
        echo "   {$user['name']} ({$user['email']}): {$user['role_name']} (ID: {$user['role_id']})\n";
    }
    
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "🎉 ROLE FIX COMPLETED SUCCESSFULLY!\n";
    echo str_repeat("=", 60) . "\n";
    
    echo "\n✅ WHAT WAS FIXED:\n";
    echo "   • Role ID 1: Super Admin -> slug: 'super-admin'\n";
    echo "   • Role ID 2: Admin -> slug: 'admin'\n";
    echo "   • Role ID 3: User -> slug: 'user'\n";
    echo "   • Ensured admin users exist\n";
    
    echo "\n🔗 ADMIN ROUTES SHOULD NOW WORK:\n";
    echo "   • http://nelly-elearning.test/admin/state-transmissions\n";
    echo "   • http://nelly-elearning.test/admin/certificates\n";
    echo "   • http://nelly-elearning.test/admin/users\n";
    echo "   • http://nelly-elearning.test/admin/dashboard\n";
    echo "   • http://nelly-elearning.test/booklets\n";
    
    echo "\n⚠️  NEXT STEPS:\n";
    echo "   • Clear browser cache and cookies\n";
    echo "   • Log out and log back in\n";
    echo "   • Test admin routes\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

?>