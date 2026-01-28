<?php

/**
 * SYSTEM VERIFICATION AFTER ROLE FIX
 * This script verifies that all systems are working after fixing the 403 errors
 */

echo "🔍 SYSTEM VERIFICATION AFTER ROLE FIX\n";
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
    
    // 1. Verify Role System Fix
    echo "1. 🔐 ROLE SYSTEM VERIFICATION:\n";
    echo str_repeat("-", 35) . "\n";
    
    $stmt = $pdo->prepare("SELECT * FROM roles ORDER BY id");
    $stmt->execute();
    $roles = $stmt->fetchAll();
    
    $roleSystemFixed = true;
    $expectedRoles = [
        1 => ['name' => 'Super Admin', 'slug' => 'super-admin'],
        2 => ['name' => 'Admin', 'slug' => 'admin'],
        3 => ['name' => 'User', 'slug' => 'user']
    ];
    
    foreach ($roles as $role) {
        $expected = $expectedRoles[$role['id']] ?? null;
        if ($expected && $role['name'] === $expected['name'] && $role['slug'] === $expected['slug']) {
            echo "   ✅ Role ID {$role['id']}: {$role['name']} -> {$role['slug']}\n";
        } else {
            echo "   ❌ Role ID {$role['id']}: {$role['name']} -> {$role['slug']} (INCORRECT)\n";
            $roleSystemFixed = false;
        }
    }
    
    // Check admin users
    $stmt = $pdo->prepare("
        SELECT u.id, u.name, u.email, u.role_id, r.name as role_name, r.slug as role_slug
        FROM users u
        LEFT JOIN roles r ON u.role_id = r.id
        WHERE u.role_id IN (1, 2)
        ORDER BY u.id
    ");
    $stmt->execute();
    $adminUsers = $stmt->fetchAll();
    
    echo "\n   👥 ADMIN USERS:\n";
    if (empty($adminUsers)) {
        echo "   ❌ NO ADMIN USERS FOUND!\n";
        $roleSystemFixed = false;
    } else {
        foreach ($adminUsers as $user) {
            echo "   ✅ {$user['name']} ({$user['email']}): {$user['role_name']} (ID: {$user['role_id']})\n";
        }
    }
    
    echo "\n   🔐 ROLE SYSTEM STATUS: " . ($roleSystemFixed ? "✅ FIXED" : "❌ NEEDS ATTENTION") . "\n";
    
    // 2. Verify Critical Tables
    echo "\n2. 🗄️  CRITICAL TABLES VERIFICATION:\n";
    echo str_repeat("-", 40) . "\n";
    
    $criticalTables = [
        'users' => 'User accounts',
        'roles' => 'User roles',
        'user_course_enrollments' => 'Course enrollments',
        'courses' => 'Course catalog',
        'chapters' => 'Course chapters',
        'chapter_questions' => 'Chapter questions',
        'booklet_orders' => 'Booklet orders',
        'booklets' => 'Booklet catalog',
        'state_transmissions' => 'State submissions',
        'certificates' => 'Certificate records',
        'payments' => 'Payment records'
    ];
    
    $missingTables = [];
    $tablesOk = 0;
    
    foreach ($criticalTables as $table => $description) {
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM $table");
            $stmt->execute();
            $count = $stmt->fetch()['count'];
            echo "   ✅ $table ($count records) - $description\n";
            $tablesOk++;
        } catch (Exception $e) {
            echo "   ❌ $table - MISSING! ($description)\n";
            $missingTables[] = $table;
        }
    }
    
    echo "\n   📊 TABLES STATUS: $tablesOk/" . count($criticalTables) . " tables OK\n";
    
    // 3. Test Admin Route Compatibility
    echo "\n3. 🛤️  ADMIN ROUTE COMPATIBILITY:\n";
    echo str_repeat("-", 35) . "\n";
    
    echo "   A. AdminMiddleware (checks role_id 1,2):\n";
    foreach ($adminUsers as $user) {
        $roleId = $user['role_id'];
        $canAccess = in_array($roleId, [1, 2]);
        $status = $canAccess ? '✅ ALLOWED' : '❌ DENIED';
        echo "      {$user['name']} (role_id: $roleId): $status\n";
    }
    
    echo "\n   B. RoleMiddleware (checks slugs 'super-admin','admin'):\n";
    foreach ($adminUsers as $user) {
        $roleSlug = $user['role_slug'];
        $canAccess = in_array($roleSlug, ['super-admin', 'admin']);
        $status = $canAccess ? '✅ ALLOWED' : '❌ DENIED';
        echo "      {$user['name']} (slug: $roleSlug): $status\n";
    }
    
    // 4. Check for Common Issues
    echo "\n4. ⚠️  COMMON ISSUES CHECK:\n";
    echo str_repeat("-", 25) . "\n";
    
    $issues = [];
    
    // Check for duplicate routes
    echo "   🔍 Checking for potential issues...\n";
    
    // Check if booklet_orders table exists (common error)
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM booklet_orders");
        $stmt->execute();
        echo "   ✅ Booklet system tables exist\n";
    } catch (Exception $e) {
        echo "   ❌ Booklet system tables missing\n";
        $issues[] = "Booklet system tables missing";
    }
    
    // Check middleware registration
    $middlewareFile = __DIR__ . '/bootstrap/app.php';
    if (file_exists($middlewareFile)) {
        $content = file_get_contents($middlewareFile);
        if (strpos($content, "'admin' => \\App\\Http\\Middleware\\AdminMiddleware::class") !== false) {
            echo "   ✅ AdminMiddleware registered\n";
        } else {
            echo "   ❌ AdminMiddleware not registered\n";
            $issues[] = "AdminMiddleware not registered";
        }
    }
    
    // 5. Final Status Report
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "📋 FINAL SYSTEM STATUS REPORT\n";
    echo str_repeat("=", 60) . "\n";
    
    $overallStatus = $roleSystemFixed && empty($missingTables) && empty($issues);
    
    echo "\n🔐 ROLE SYSTEM: " . ($roleSystemFixed ? "✅ WORKING" : "❌ NEEDS FIX") . "\n";
    echo "🗄️  DATABASE TABLES: " . (empty($missingTables) ? "✅ ALL PRESENT" : "❌ " . count($missingTables) . " MISSING") . "\n";
    echo "⚠️  SYSTEM ISSUES: " . (empty($issues) ? "✅ NONE FOUND" : "❌ " . count($issues) . " FOUND") . "\n";
    
    echo "\n🎯 OVERALL STATUS: " . ($overallStatus ? "✅ SYSTEM READY" : "❌ NEEDS ATTENTION") . "\n";
    
    if (!$overallStatus) {
        echo "\n🔧 REQUIRED ACTIONS:\n";
        
        if (!$roleSystemFixed) {
            echo "   1. Fix role system using the SQL commands in ADMIN_403_ERRORS_COMPLETE_FIX.md\n";
        }
        
        if (!empty($missingTables)) {
            echo "   2. Create missing tables: " . implode(', ', $missingTables) . "\n";
        }
        
        if (!empty($issues)) {
            echo "   3. Resolve issues: " . implode(', ', $issues) . "\n";
        }
    } else {
        echo "\n🎉 SYSTEM IS READY FOR TESTING!\n";
        echo "\n🔗 TEST THESE ADMIN ROUTES:\n";
        echo "   • http://nelly-elearning.test/admin/state-transmissions\n";
        echo "   • http://nelly-elearning.test/admin/certificates\n";
        echo "   • http://nelly-elearning.test/admin/users\n";
        echo "   • http://nelly-elearning.test/admin/dashboard\n";
        echo "   • http://nelly-elearning.test/booklets\n";
        
        echo "\n⚠️  REMEMBER TO:\n";
        echo "   • Clear browser cache and cookies\n";
        echo "   • Log out and log back in\n";
        echo "   • Test each admin module\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

?>