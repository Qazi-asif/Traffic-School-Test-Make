<?php

/**
 * COMPREHENSIVE 403 ERROR DIAGNOSIS
 * This script diagnoses all permission and role issues causing 403 errors
 */

echo "🔍 COMPREHENSIVE 403 ERROR DIAGNOSIS - Starting...\n";
echo str_repeat("=", 80) . "\n";

$host = '127.0.0.1';
$port = '3306';
$database = 'nelly-elearning';
$username = 'root';
$password = '';

$issues = [];
$fixes = [];

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Database connection established\n\n";
    
    // Test 1: Check User Roles System
    echo "1. 🔐 CHECKING USER ROLES SYSTEM\n";
    echo str_repeat("-", 40) . "\n";
    
    // Check if roles table exists
    $stmt = $pdo->prepare("SHOW TABLES LIKE 'roles'");
    $stmt->execute();
    $rolesTableExists = $stmt->fetch();
    
    if (!$rolesTableExists) {
        echo "❌ roles table: MISSING\n";
        $issues[] = "roles table is missing";
        $fixes[] = "Create roles table with admin roles";
    } else {
        echo "✅ roles table: EXISTS\n";
        
        // Check roles data
        $stmt = $pdo->prepare("SELECT * FROM roles");
        $stmt->execute();
        $roles = $stmt->fetchAll();
        
        echo "📊 Available roles:\n";
        foreach ($roles as $role) {
            echo "   - ID: {$role['id']}, Name: {$role['name']}, Slug: {$role['slug']}\n";
        }
        
        // Check for required admin roles
        $requiredRoles = [
            ['id' => 1, 'name' => 'Super Admin', 'slug' => 'super-admin'],
            ['id' => 2, 'name' => 'Admin', 'slug' => 'admin'],
            ['id' => 3, 'name' => 'User', 'slug' => 'user']
        ];
        
        $existingRoleIds = array_column($roles, 'id');
        foreach ($requiredRoles as $required) {
            if (!in_array($required['id'], $existingRoleIds)) {
                echo "❌ Missing required role: {$required['name']} (ID: {$required['id']})\n";
                $issues[] = "Missing role: {$required['name']}";
                $fixes[] = "Add role: {$required['name']} with ID {$required['id']}";
            } else {
                echo "✅ Required role exists: {$required['name']}\n";
            }
        }
    }
    
    // Test 2: Check User Role Assignments
    echo "\n2. 👥 CHECKING USER ROLE ASSIGNMENTS\n";
    echo str_repeat("-", 40) . "\n";
    
    $stmt = $pdo->prepare("SELECT id, name, email, role_id FROM users");
    $stmt->execute();
    $users = $stmt->fetchAll();
    
    echo "📊 User role assignments:\n";
    $adminUsers = 0;
    $regularUsers = 0;
    $unassignedUsers = 0;
    
    foreach ($users as $user) {
        $roleStatus = '';
        if ($user['role_id'] == 1) {
            $roleStatus = 'Super Admin ✅';
            $adminUsers++;
        } elseif ($user['role_id'] == 2) {
            $roleStatus = 'Admin ✅';
            $adminUsers++;
        } elseif ($user['role_id'] == 3) {
            $roleStatus = 'User';
            $regularUsers++;
        } else {
            $roleStatus = 'UNASSIGNED ❌';
            $unassignedUsers++;
            $issues[] = "User {$user['email']} has no valid role_id ({$user['role_id']})";
            $fixes[] = "Assign proper role_id to user {$user['email']}";
        }
        
        echo "   - {$user['name']} ({$user['email']}): $roleStatus\n";
    }
    
    echo "\n📈 Role distribution:\n";
    echo "   - Admin users (can access admin): $adminUsers\n";
    echo "   - Regular users: $regularUsers\n";
    echo "   - Unassigned users: $unassignedUsers\n";
    
    if ($adminUsers == 0) {
        echo "❌ CRITICAL: No admin users found!\n";
        $issues[] = "No admin users - all admin routes will return 403";
        $fixes[] = "Assign admin role to at least one user";
    }
    
    // Test 3: Check Middleware Logic
    echo "\n3. 🛡️  CHECKING MIDDLEWARE LOGIC\n";
    echo str_repeat("-", 40) . "\n";
    
    // Check AdminMiddleware file
    if (file_exists('app/Http/Middleware/AdminMiddleware.php')) {
        echo "✅ AdminMiddleware file: EXISTS\n";
        
        $middlewareContent = file_get_contents('app/Http/Middleware/AdminMiddleware.php');
        
        // Check role_id logic
        if (strpos($middlewareContent, 'role_id') !== false) {
            echo "✅ AdminMiddleware uses role_id: CORRECT\n";
            
            // Check which role_ids are allowed
            if (strpos($middlewareContent, '[1, 2]') !== false) {
                echo "✅ AdminMiddleware allows role_id 1,2: CORRECT\n";
            } else {
                echo "❌ AdminMiddleware role_id check: INCORRECT\n";
                $issues[] = "AdminMiddleware doesn't allow correct role_ids";
                $fixes[] = "Fix AdminMiddleware to allow role_id 1 and 2";
            }
        } else {
            echo "❌ AdminMiddleware role_id check: MISSING\n";
            $issues[] = "AdminMiddleware doesn't check role_id";
            $fixes[] = "Add role_id checking to AdminMiddleware";
        }
    } else {
        echo "❌ AdminMiddleware file: MISSING\n";
        $issues[] = "AdminMiddleware file is missing";
        $fixes[] = "Create AdminMiddleware file";
    }
    
    // Test 4: Check Route Middleware Configuration
    echo "\n4. 🛤️  CHECKING ROUTE MIDDLEWARE CONFIGURATION\n";
    echo str_repeat("-", 40) . "\n";
    
    if (file_exists('routes/web.php')) {
        echo "✅ routes/web.php file: EXISTS\n";
        
        $routesContent = file_get_contents('routes/web.php');
        
        // Count different middleware patterns
        $adminMiddlewareCount = substr_count($routesContent, "'admin'");
        $roleMiddlewareCount = substr_count($routesContent, "'role:super-admin,admin'");
        $authAdminCount = substr_count($routesContent, "['auth', 'admin']");
        
        echo "📊 Route middleware usage:\n";
        echo "   - 'admin' middleware: $adminMiddlewareCount routes\n";
        echo "   - 'role:super-admin,admin' middleware: $roleMiddlewareCount routes\n";
        echo "   - ['auth', 'admin'] middleware: $authAdminCount routes\n";
        
        // Check for problematic patterns
        if (strpos($routesContent, "'role:admin'") !== false) {
            echo "⚠️  Found 'role:admin' pattern - might cause issues\n";
            $issues[] = "Some routes use 'role:admin' which might not work";
            $fixes[] = "Change 'role:admin' to 'role:super-admin,admin'";
        }
    } else {
        echo "❌ routes/web.php file: MISSING\n";
        $issues[] = "routes/web.php file is missing";
    }
    
    // Test 5: Check Bootstrap Middleware Registration
    echo "\n5. ⚙️  CHECKING BOOTSTRAP MIDDLEWARE REGISTRATION\n";
    echo str_repeat("-", 40) . "\n";
    
    if (file_exists('bootstrap/app.php')) {
        echo "✅ bootstrap/app.php file: EXISTS\n";
        
        $bootstrapContent = file_get_contents('bootstrap/app.php');
        
        $middlewareAliases = [
            'admin' => 'AdminMiddleware',
            'role' => 'RoleMiddleware',
            'super-admin' => 'SuperAdminMiddleware'
        ];
        
        foreach ($middlewareAliases as $alias => $class) {
            if (strpos($bootstrapContent, "'$alias'") !== false) {
                echo "✅ '$alias' middleware alias: REGISTERED\n";
            } else {
                echo "❌ '$alias' middleware alias: MISSING\n";
                $issues[] = "'$alias' middleware alias not registered";
                $fixes[] = "Register '$alias' middleware alias in bootstrap/app.php";
            }
        }
    } else {
        echo "❌ bootstrap/app.php file: MISSING\n";
        $issues[] = "bootstrap/app.php file is missing";
    }
    
    // Test 6: Simulate Admin Access Check
    echo "\n6. 🧪 SIMULATING ADMIN ACCESS CHECK\n";
    echo str_repeat("-", 40) . "\n";
    
    foreach ($users as $user) {
        $canAccessAdmin = in_array($user['role_id'], [1, 2]);
        $status = $canAccessAdmin ? '✅ ALLOWED' : '❌ DENIED (403)';
        echo "   - {$user['name']}: $status\n";
        
        if (!$canAccessAdmin && strpos($user['email'], 'admin') !== false) {
            $issues[] = "User {$user['email']} looks like admin but can't access admin routes";
            $fixes[] = "Set role_id to 1 or 2 for user {$user['email']}";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Critical Error: " . $e->getMessage() . "\n";
    $issues[] = "Database connection or query error: " . $e->getMessage();
}

// Generate Summary and Fixes
echo "\n" . str_repeat("=", 80) . "\n";
echo "📋 DIAGNOSIS SUMMARY\n";
echo str_repeat("=", 80) . "\n";

echo "\n❌ ISSUES FOUND (" . count($issues) . "):\n";
foreach ($issues as $i => $issue) {
    echo "   " . ($i + 1) . ". $issue\n";
}

echo "\n🔧 RECOMMENDED FIXES (" . count($fixes) . "):\n";
foreach ($fixes as $i => $fix) {
    echo "   " . ($i + 1) . ". $fix\n";
}

if (empty($issues)) {
    echo "\n🎉 NO ISSUES FOUND!\n";
    echo "The 403 errors might be due to:\n";
    echo "   - User not logged in\n";
    echo "   - User doesn't have admin role\n";
    echo "   - Session/authentication issues\n";
} else {
    echo "\n⚠️  ISSUES DETECTED!\n";
    echo "These issues are likely causing the 403 errors.\n";
    echo "Run the fix script to resolve them.\n";
}

echo "\n" . str_repeat("=", 80) . "\n";
echo "Diagnosis completed at: " . date('Y-m-d H:i:s') . "\n";

?>