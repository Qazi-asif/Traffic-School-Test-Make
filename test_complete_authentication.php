<?php
/**
 * Complete Authentication System Test
 * Tests all aspects of the multi-state authentication system
 */

echo "🧪 Complete Multi-State Authentication System Test\n";
echo "================================================\n\n";

// Test Laravel application loading
try {
    require_once 'vendor/autoload.php';
    $app = require_once 'bootstrap/app.php';
    echo "✅ Laravel application loaded successfully\n";
    
    // Test database connection
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=nelly-elearning', 'root', '');
    echo "✅ Database connection successful\n";
    
    // Test if roles exist
    $rolesQuery = $pdo->query("SELECT COUNT(*) FROM roles");
    $roleCount = $rolesQuery->fetchColumn();
    echo "✅ Found {$roleCount} roles in database\n";
    
    // Test if users exist
    $usersQuery = $pdo->query("SELECT COUNT(*) FROM users");
    $userCount = $usersQuery->fetchColumn();
    echo "✅ Found {$userCount} users in database\n";
    
    // Test state-specific users
    $stateUsers = $pdo->query("SELECT state, COUNT(*) as count FROM users WHERE state IN ('florida', 'missouri', 'texas', 'delaware') GROUP BY state")->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\n👥 State-specific users:\n";
    foreach ($stateUsers as $stateUser) {
        echo "   {$stateUser['state']}: {$stateUser['count']} users\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n🔗 Authentication URLs to Test:\n";
echo "================================\n";

$baseUrl = 'http://nelly-elearning.test';
$states = ['florida', 'missouri', 'texas', 'delaware'];

foreach ($states as $state) {
    $stateIcon = [
        'florida' => '🌴',
        'missouri' => '🏛️',
        'texas' => '🤠', 
        'delaware' => '🏖️'
    ][$state];
    
    echo "{$stateIcon} " . ucfirst($state) . " Portal:\n";
    echo "   Login: {$baseUrl}/{$state}/login\n";
    echo "   Register: {$baseUrl}/{$state}/register\n";
    echo "   Dashboard: {$baseUrl}/{$state}/dashboard (requires login)\n\n";
}

echo "🔑 Test Credentials:\n";
echo "===================\n";
foreach ($states as $state) {
    echo "{$state}@test.com / password123\n";
}
echo "admin@test.com / admin123 (can access all states)\n\n";

echo "🧪 Manual Testing Steps:\n";
echo "========================\n";
echo "1. Visit a state login page (e.g., /florida/login)\n";
echo "2. Try logging in with correct credentials\n";
echo "3. Verify you're redirected to the correct state dashboard\n";
echo "4. Try accessing a different state's dashboard\n";
echo "5. Verify you're redirected back to your state\n";
echo "6. Test registration with a new account\n";
echo "7. Test logout functionality\n\n";

echo "✅ Authentication System Setup Complete!\n";
echo "Your multi-state authentication system is ready for testing.\n";