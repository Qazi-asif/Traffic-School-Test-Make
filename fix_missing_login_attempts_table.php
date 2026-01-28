<?php

/**
 * Fix Missing login_attempts Table
 * 
 * This script creates the missing login_attempts table that's preventing login
 */

echo "🔧 FIXING MISSING LOGIN_ATTEMPTS TABLE\n";
echo "=====================================\n\n";

try {
    // Connect to database
    $pdo = new PDO(
        "mysql:host=127.0.0.1;dbname=nelly-elearning", 
        "root", 
        "", 
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "✅ Database connection successful\n\n";
    
    // Check if login_attempts table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'login_attempts'");
    if ($stmt->rowCount() > 0) {
        echo "✅ login_attempts table already exists\n";
    } else {
        echo "🔧 Creating login_attempts table...\n";
        
        // Create login_attempts table
        $sql = "
        CREATE TABLE `login_attempts` (
            `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            `email` varchar(255) NOT NULL,
            `ip_address` varchar(45) NOT NULL,
            `user_agent` text,
            `successful` tinyint(1) NOT NULL DEFAULT 0,
            `attempted_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `created_at` timestamp NULL DEFAULT NULL,
            `updated_at` timestamp NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `login_attempts_email_index` (`email`),
            KEY `login_attempts_ip_address_index` (`ip_address`),
            KEY `login_attempts_attempted_at_index` (`attempted_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
        
        $pdo->exec($sql);
        echo "✅ login_attempts table created successfully\n";
    }
    
    // Also create test user while we're here
    echo "\n🔧 Creating test user...\n";
    
    // Check if test user exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute(['test@example.com']);
    
    if ($stmt->fetch()) {
        echo "✅ Test user already exists\n";
    } else {
        // Create test user with Laravel's default password hash for 'password'
        $stmt = $pdo->prepare("
            INSERT INTO users (
                name, email, password, state_code, email_verified_at, created_at, updated_at
            ) VALUES (?, ?, ?, ?, NOW(), NOW(), NOW())
        ");
        
        $stmt->execute([
            'Test User',
            'test@example.com',
            '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password: 'password'
            'florida'
        ]);
        
        echo "✅ Test user created successfully\n";
    }
    
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "✅ LOGIN FIX COMPLETE!\n";
    echo str_repeat("=", 50) . "\n\n";
    
    echo "🔗 NOW YOU CAN LOGIN:\n";
    echo "   URL: http://nelly-elearning.test\n";
    echo "   Email: test@example.com\n";
    echo "   Password: password\n\n";
    
    echo "🎯 WHAT WILL HAPPEN:\n";
    echo "1. Login will work (no more table error)\n";
    echo "2. You'll be redirected to /dashboard\n";
    echo "3. Dashboard will redirect to /florida\n";
    echo "4. You'll see the professional Florida portal\n\n";
    
    echo "✅ READY TO TEST THE UI/UX SYSTEM!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "\nPlease check your database connection and try again.\n";
}

?>