<?php
/**
 * Test Authentication System
 * Verify that state-specific authentication routes are working
 */

echo "🧪 Testing Multi-State Authentication System\n";
echo "==========================================\n\n";

// Test URLs
$baseUrl = 'http://nelly-elearning.test';

$authRoutes = [
    'Florida Login' => '/florida/login',
    'Missouri Login' => '/missouri/login', 
    'Texas Login' => '/texas/login',
    'Delaware Login' => '/delaware/login',
    'Florida Register' => '/florida/register',
    'Missouri Register' => '/missouri/register',
    'Texas Register' => '/texas/register',
    'Delaware Register' => '/delaware/register'
];

foreach ($authRoutes as $name => $route) {
    $url = $baseUrl . $route;
    echo "Testing: {$name}\n";
    echo "URL: {$url}\n";
    
    // Use curl to test the route
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HEADER, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        echo "❌ Error: {$error}\n";
    } elseif ($httpCode === 200) {
        echo "✅ Success (HTTP {$httpCode})\n";
        // Check if response contains expected state name
        if (strpos($response, ucfirst(explode('/', $route)[1])) !== false) {
            echo "✅ State-specific content detected\n";
        }
    } else {
        echo "⚠️  HTTP {$httpCode}\n";
    }
    
    echo "---\n";
}

echo "\n🎯 Next Steps:\n";
echo "1. Visit the login URLs in your browser\n";
echo "2. Test registration with new accounts\n";
echo "3. Test login with existing accounts:\n";
echo "   - florida@test.com / password123\n";
echo "   - missouri@test.com / password123\n";
echo "   - texas@test.com / password123\n";
echo "   - delaware@test.com / password123\n";
echo "4. Verify state-specific dashboards work after login\n";