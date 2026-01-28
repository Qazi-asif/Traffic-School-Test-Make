<?php

/**
 * Test System After Fixes
 * 
 * This script tests if all the fixes are working properly
 */

echo "🧪 TESTING SYSTEM AFTER FIXES\n";
echo str_repeat("=", 40) . "\n\n";

try {
    // Test 1: Check if we can connect to database
    echo "TEST 1: Database Connection\n";
    $pdo = new PDO(
        "mysql:host=127.0.0.1;dbname=nelly-elearning", 
        "root", 
        "", 
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "✅ Database connection successful\n\n";
    
    // Test 2: Check if roles table exists
    echo "TEST 2: Roles Table\n";
    try {
        $rolesCount = $pdo->query("SELECT COUNT(*) FROM roles")->fetchColumn();
        echo "✅ Roles table exists with {$rolesCount} roles\n";
        
        $roles = $pdo->query("SELECT name, slug FROM roles")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($roles as $role) {
            echo "   • {$role['name']} ({$role['slug']})\n";
        }
    } catch (Exception $e) {
        echo "❌ Roles table issue: " . $e->getMessage() . "\n";
        echo "   Please run the roles creation SQL first\n";
    }
    echo "\n";
    
    // Test 3: Check test user
    echo "TEST 3: Test User\n";
    try {
        $testUser = $pdo->query("
            SELECT u.*, r.name as role_name, r.slug as role_slug 
            FROM users u 
            LEFT JOIN roles r ON u.role_id = r.id 
            WHERE u.email = 'test@example.com' 
            LIMIT 1
        ")->fetch(PDO::FETCH_ASSOC);
        
        if ($testUser) {
            echo "✅ Test user exists\n";
            echo "   • Name: {$testUser['name']}\n";
            echo "   • Email: {$testUser['email']}\n";
            echo "   • Role: " . ($testUser['role_name'] ?? 'No role') . "\n";
            echo "   • Role Slug: " . ($testUser['role_slug'] ?? 'No slug') . "\n";
        } else {
            echo "❌ Test user not found\n";
            echo "   Please create test user or run emergency login\n";
        }
    } catch (Exception $e) {
        echo "❌ Test user check failed: " . $e->getMessage() . "\n";
    }
    echo "\n";
    
    // Test 4: Check state tables
    echo "TEST 4: State-Specific Course Tables\n";
    $stateTables = [
        'florida_courses',
        'missouri_courses', 
        'texas_courses',
        'delaware_courses',
        'nevada_courses'
    ];
    
    foreach ($stateTables as $table) {
        try {
            $count = $pdo->query("SELECT COUNT(*) FROM {$table}")->fetchColumn();
            echo "✅ {$table}: {$count} records\n";
        } catch (Exception $e) {
            echo "❌ {$table}: Table missing or error\n";
        }
    }
    echo "\n";
    
    // Test 5: Check enrollments
    echo "TEST 5: User Course Enrollments\n";
    try {
        $enrollmentsCount = $pdo->query("SELECT COUNT(*) FROM user_course_enrollments")->fetchColumn();
        $enrollmentsWithTable = $pdo->query("SELECT COUNT(*) FROM user_course_enrollments WHERE course_table IS NOT NULL")->fetchColumn();
        
        echo "✅ Total enrollments: {$enrollmentsCount}\n";
        echo "✅ Enrollments with course_table: {$enrollmentsWithTable}\n";
    } catch (Exception $e) {
        echo "❌ Enrollments check failed: " . $e->getMessage() . "\n";
    }
    echo "\n";
    
    echo str_repeat("=", 40) . "\n";
    echo "🎯 SYSTEM STATUS SUMMARY\n";
    echo str_repeat("=", 40) . "\n\n";
    
    echo "✅ READY TO TEST:\n";
    echo "   URL: http://nelly-elearning.test/emergency-login\n";
    echo "   Expected: Beautiful dashboard without errors\n\n";
    
    echo "🔧 IF ISSUES REMAIN:\n";
    echo "   1. Run the roles table SQL commands\n";
    echo "   2. Ensure test user has admin role\n";
    echo "   3. Check that User model syntax is correct\n\n";
    
} catch (Exception $e) {
    echo "❌ System test failed: " . $e->getMessage() . "\n";
    echo "Please check your database configuration.\n\n";
}

?>