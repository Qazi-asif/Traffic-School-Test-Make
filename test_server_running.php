<?php
/**
 * Test if Laravel Server is Running
 * Quick check to see if the application is accessible
 */

echo "🔍 Testing Laravel Server Status\n";
echo "===============================\n\n";

$testUrl = 'http://127.0.0.1:8000';

echo "Testing server at: {$testUrl}\n";

// Test basic connectivity
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $testUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
curl_setopt($ch, CURLOPT_HEADER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo "❌ Server not accessible: {$error}\n\n";
    
    echo "🚀 TO START THE SERVER:\n";
    echo "======================\n";
    echo "Option 1: Run this command in a new terminal:\n";
    echo "   php artisan serve --host=127.0.0.1 --port=8000\n\n";
    
    echo "Option 2: Double-click the file:\n";
    echo "   start_laravel_server.bat\n\n";
    
    echo "Option 3: Use simple PHP server:\n";
    echo "   php -S 127.0.0.1:8000 -t public\n\n";
    
} elseif ($httpCode === 200) {
    echo "✅ Server is running successfully!\n";
    echo "✅ HTTP Status: {$httpCode}\n\n";
    
    echo "🎉 YOUR APPLICATION IS READY!\n";
    echo "============================\n\n";
    
    echo "🔑 LOGIN URLS (Click to open):\n";
    echo "- Florida Portal: http://127.0.0.1:8000/florida/login\n";
    echo "- Missouri Portal: http://127.0.0.1:8000/missouri/login\n";
    echo "- Texas Portal: http://127.0.0.1:8000/texas/login\n";
    echo "- Delaware Portal: http://127.0.0.1:8000/delaware/login\n\n";
    
    echo "👤 TEST CREDENTIALS:\n";
    echo "Email: florida@test.com\n";
    echo "Password: password123\n\n";
    
    echo "📋 WHAT YOU CAN TEST:\n";
    echo "✅ Login to any state portal\n";
    echo "✅ View state-specific dashboards\n";
    echo "✅ Test course progress system\n";
    echo "✅ Generate certificates\n";
    echo "✅ Use progress monitoring APIs\n\n";
    
    // Test specific login URLs
    echo "🧪 Testing Login URLs:\n";
    $loginUrls = [
        'Florida' => '/florida/login',
        'Missouri' => '/missouri/login',
        'Texas' => '/texas/login',
        'Delaware' => '/delaware/login'
    ];
    
    foreach ($loginUrls as $state => $path) {
        $url = $testUrl . $path;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            echo "✅ {$state} login page: Working\n";
        } else {
            echo "⚠️  {$state} login page: HTTP {$httpCode}\n";
        }
    }
    
} else {
    echo "⚠️  Server responded with HTTP {$httpCode}\n";
    echo "This might indicate a configuration issue.\n";
}

echo "\n🏁 Server test completed\n";