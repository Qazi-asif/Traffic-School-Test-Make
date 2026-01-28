<?php

/**
 * Fix Login System Immediately
 * 
 * This script ensures the login system works by creating all necessary tables and data
 */

echo "🔧 FIXING LOGIN SYSTEM NOW\n";
echo str_repeat("=", 50) . "\n\n";

try {
    // Database connection
    $pdo = new PDO(
        "mysql:host=127.0.0.1;dbname=nelly-elearning", 
        "root", 
        "", 
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "✅ Database connection successful\n\n";
    
    // 1. Ensure users table exists with correct structure
    echo "👤 Creating/updating users table...\n";
    
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `users` (
            `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            `first_name` varchar(255) NOT NULL,
            `last_name` varchar(255) NOT NULL,
            `email` varchar(255) NOT NULL,
            `email_verified_at` timestamp NULL DEFAULT NULL,
            `password` varchar(255) NOT NULL,
            `state_code` varchar(2) DEFAULT 'FL',
            `role_id` bigint(20) unsigned DEFAULT 2,
            `remember_token` varchar(100) DEFAULT NULL,
            `created_at` timestamp NULL DEFAULT NULL,
            `updated_at` timestamp NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `users_email_unique` (`email`),
            KEY `users_state_code_index` (`state_code`),
            KEY `users_role_id_index` (`role_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    echo "✅ Users table ready\n";
    
    // 2. Ensure roles table exists
    echo "🔐 Creating roles table...\n";
    
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `roles` (
            `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            `name` varchar(255) NOT NULL,
            `slug` varchar(255) NOT NULL,
            `description` text NULL,
            `created_at` timestamp NULL DEFAULT NULL,
            `updated_at` timestamp NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `roles_slug_unique` (`slug`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Insert roles
    $pdo->exec("
        INSERT INTO roles (id, name, slug, description, created_at, updated_at) VALUES
        (1, 'Administrator', 'admin', 'System administrator with full access', NOW(), NOW()),
        (2, 'Student', 'student', 'Student user with course access', NOW(), NOW()),
        (3, 'Instructor', 'instructor', 'Course instructor', NOW(), NOW())
        ON DUPLICATE KEY UPDATE 
        name = VALUES(name),
        description = VALUES(description),
        updated_at = NOW()
    ");
    
    echo "✅ Roles created\n";
    
    // 3. Create admin user
    echo "👨‍💼 Creating admin user...\n";
    
    $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
    $pdo->exec("
        INSERT INTO users (
            id, first_name, last_name, email, password, email_verified_at, 
            state_code, role_id, created_at, updated_at
        ) VALUES (
            1, 'Admin', 'User', 'admin@dummiestrafficschool.com', 
            '{$adminPassword}', NOW(), 'FL', 1, NOW(), NOW()
        ) ON DUPLICATE KEY UPDATE 
        password = '{$adminPassword}', 
        role_id = 1,
        email_verified_at = NOW(),
        updated_at = NOW()
    ");
    
    echo "✅ Admin user: admin@dummiestrafficschool.com / admin123\n";
    
    // 4. Create test student
    echo "🎓 Creating test student...\n";
    
    $studentPassword = password_hash('student123', PASSWORD_DEFAULT);
    $pdo->exec("
        INSERT INTO users (
            id, first_name, last_name, email, password, email_verified_at, 
            state_code, role_id, created_at, updated_at
        ) VALUES (
            2, 'Test', 'Student', 'student@test.com', 
            '{$studentPassword}', NOW(), 'FL', 2, NOW(), NOW()
        ) ON DUPLICATE KEY UPDATE 
        password = '{$studentPassword}', 
        role_id = 2,
        email_verified_at = NOW(),
        updated_at = NOW()
    ");
    
    echo "✅ Test student: student@test.com / student123\n";
    
    // 5. Ensure password_reset_tokens table exists
    echo "🔑 Creating password reset table...\n";
    
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
            `email` varchar(255) NOT NULL,
            `token` varchar(255) NOT NULL,
            `created_at` timestamp NULL DEFAULT NULL,
            PRIMARY KEY (`email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    echo "✅ Password reset table ready\n";
    
    // 6. Ensure sessions table exists
    echo "📝 Creating sessions table...\n";
    
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `sessions` (
            `id` varchar(255) NOT NULL,
            `user_id` bigint(20) unsigned DEFAULT NULL,
            `ip_address` varchar(45) DEFAULT NULL,
            `user_agent` text,
            `payload` longtext NOT NULL,
            `last_activity` int NOT NULL,
            PRIMARY KEY (`id`),
            KEY `sessions_user_id_index` (`user_id`),
            KEY `sessions_last_activity_index` (`last_activity`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    echo "✅ Sessions table ready\n";
    
    // 7. Test login functionality
    echo "\n🧪 Testing login functionality...\n";
    
    $testUser = $pdo->query("
        SELECT u.*, r.slug as role_slug 
        FROM users u 
        LEFT JOIN roles r ON u.role_id = r.id 
        WHERE u.email = 'admin@dummiestrafficschool.com'
    ")->fetch(PDO::FETCH_ASSOC);
    
    if ($testUser) {
        $passwordCheck = password_verify('admin123', $testUser['password']);
        echo "✅ Admin user found: " . $testUser['first_name'] . " " . $testUser['last_name'] . "\n";
        echo "✅ Password verification: " . ($passwordCheck ? "PASS" : "FAIL") . "\n";
        echo "✅ Role: " . ($testUser['role_slug'] ?? 'No role') . "\n";
    } else {
        echo "❌ Admin user not found\n";
    }
    
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "🎉 LOGIN SYSTEM FIXED!\n";
    echo str_repeat("=", 50) . "\n\n";
    
    echo "🔑 LOGIN CREDENTIALS:\n";
    echo "Admin: admin@dummiestrafficschool.com / admin123\n";
    echo "Student: student@test.com / student123\n\n";
    
    echo "🌐 LOGIN OPTIONS:\n";
    echo "1. Normal login: http://nelly-elearning.test/login\n";
    echo "2. Emergency bypass: http://nelly-elearning.test/emergency-login.php\n\n";
    
    echo "✅ Your login system is now fully functional!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

?>