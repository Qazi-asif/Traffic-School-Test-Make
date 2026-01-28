<?php
/**
 * Quick Route Testing Script
 * Tests all state-separated routes to ensure they're working
 */

echo "🧪 Testing Multi-State Route System\n";
echo "==================================\n\n";

// Test URLs (adjust domain as needed)
$baseUrl = 'http://nelly-elearning.test';

$routes = [
    'Florida Simple' => '/florida-simple',
    'Missouri Simple' => '/missouri-simple', 
    'Texas Simple' => '/texas-simple',
    'Delaware Simple' => '/delaware-simple',
    'Admin Simple' => '/admin-simple',
    'Admin Test' => '/admin/test',
    'Florida Controller' => '/florida-controller'
];

foreach ($routes as $name => $route) {
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
    } else {
        echo "⚠️  HTTP {$httpCode}\n";
    }
    
    echo "---\n";
}

echo "\n🎯 Next Steps:\n";
echo "1. All simple routes should return HTTP 200\n";
echo "2. Controller routes may need authentication setup\n";
echo "3. Visit routes in browser to see actual content\n";
echo "4. Set up login system for protected routes\n";