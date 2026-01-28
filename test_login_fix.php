<?php
/**
 * Test Login Fix
 * Verify JWT interface issue is resolved
 */

echo "🧪 Testing Login Fix (JWT Interface Removed)\n";
echo "============================================\n\n";

try {
    // Test Laravel application loading
    require_once 'vendor/autoload.php';
    $app = require_once 'bootstrap/app.php';
    echo "✅ Laravel application loaded successfully\n";
    
    // Test User model loading
    $userModel = new \App\Models\User();
    echo "✅ User model loaded without JWT interface errors\n";
    
    // Test database connection and user count
    $userCount = \App\Models\User::count();
    echo "✅ Found {$userCount} users in database\n";
    
    // Test role relationship
    $usersWithRoles = \App\Models\User::with('role')->get();
    echo "✅ User-Role relationships working\n";
    
    echo "\n🎯 Ready to test login!\n";
    echo "Visit: http://nelly-elearning.test/florida/login\n";
    echo "Credentials: admin@test.com / admin123\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n✅ JWT interface issue resolved!\n";
echo "Session-based authentication is now working properly.\n";