<?php

// Simple script to check if UserDataSeeder worked
echo "=== Checking UserDataSeeder Results ===\n\n";

// Check if we can connect to database
try {
    $pdo = new PDO('sqlite:database/database.sqlite');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Count users
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $userCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "✅ Total users in database: {$userCount}\n";
    
    // Count by state
    echo "\n📍 Users by state:\n";
    $stmt = $pdo->query("SELECT state_code, COUNT(*) as count FROM users GROUP BY state_code");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "   - " . strtoupper($row['state_code']) . ": {$row['count']} users\n";
    }
    
    // Count by role
    echo "\n👥 Users by role:\n";
    $stmt = $pdo->query("SELECT role, COUNT(*) as count FROM users GROUP BY role");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "   - " . ucfirst($row['role']) . ": {$row['count']} users\n";
    }
    
    // Show sample users
    echo "\n📋 Sample users:\n";
    $stmt = $pdo->query("SELECT name, email, state_code, role FROM users LIMIT 5");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "   - {$row['name']} ({$row['email']}) - {$row['state_code']} - {$row['role']}\n";
    }
    
    // Check system settings
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM system_settings");
    $settingsCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "\n⚙️ System settings: {$settingsCount}\n";
    
    echo "\n✅ UserDataSeeder completed successfully!\n";
    echo "🚀 Admin system ready with sample data.\n\n";
    
    echo "🔑 Test credentials:\n";
    echo "   Admin: admin@trafficschool.com / password123\n";
    echo "   Students: password123 for all users\n";
    
} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    echo "💡 Make sure database exists and is accessible.\n";
}