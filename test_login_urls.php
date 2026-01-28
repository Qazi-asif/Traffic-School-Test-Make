<?php
/**
 * Test Login URLs
 * Check if all login URLs are accessible
 */

echo "🧪 Testing Login URLs\n";
echo "====================\n\n";

$baseUrl = 'http://nelly-elearning.test';
$states = ['florida', 'missouri', 'texas', 'delaware'];

foreach ($states as $state) {
    $loginUrl = "{$baseUrl}/{$state}/login";
    $registerUrl = "{$baseUrl}/{$state}/register";
    
    echo "Testing {$state} URLs:\n";
    echo "Login: {$loginUrl}\n";
    echo "Register: {$registerUrl}\n";
    
    // Test if URLs are accessible
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $loginUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_NOBODY, true); // HEAD request only
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        echo "❌ Error: {$error}\n";
    } elseif ($httpCode === 200) {
        echo "✅ Login URL accessible (HTTP {$httpCode})\n";
    } else {
        echo "⚠️  Login URL returned HTTP {$httpCode}\n";
    }
    
    echo "---\n";
}

echo "\n🔑 Test Credentials:\n";
echo "Florida: florida@test.com / password123\n";
echo "Missouri: missouri@test.com / password123\n";
echo "Texas: texas@test.com / password123\n";
echo "Delaware: delaware@test.com / password123\n";
echo "Admin: admin@test.com / admin123\n\n";

echo "📋 Next Steps:\n";
echo "1. Visit one of the login URLs above\n";
echo "2. Try logging in with the test credentials\n";
echo "3. Check if you're redirected to the dashboard\n";
echo "4. Verify the progress system is working\n";