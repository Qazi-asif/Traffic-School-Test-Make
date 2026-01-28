<?php
/**
 * Quick Server Test
 * Test if the Laravel application is running and accessible
 */

echo "🌐 Quick Server Test\n";
echo "===================\n\n";

$baseUrl = 'http://nelly-elearning.test';

echo "Testing base URL: {$baseUrl}\n";

// Test basic connectivity
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_HEADER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo "❌ Connection Error: {$error}\n";
    echo "\n🔧 TROUBLESHOOTING:\n";
    echo "1. Make sure your Laravel server is running\n";
    echo "2. Try running: php artisan serve\n";
    echo "3. Or check if Laragon/XAMPP is running\n";
    echo "4. Verify the domain 'nelly-elearning.test' is configured\n";
} elseif ($httpCode === 200) {
    echo "✅ Server is accessible (HTTP {$httpCode})\n";
    
    // Test specific login URLs
    echo "\nTesting login URLs:\n";
    $loginUrls = [
        'Florida' => '/florida/login',
        'Missouri' => '/missouri/login',
        'Texas' => '/texas/login',
        'Delaware' => '/delaware/login'
    ];
    
    foreach ($loginUrls as $state => $path) {
        $url = $baseUrl . $path;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            echo "❌ {$state}: {$error}\n";
        } elseif ($httpCode === 200) {
            echo "✅ {$state}: Accessible\n";
        } else {
            echo "⚠️  {$state}: HTTP {$httpCode}\n";
        }
    }
    
    echo "\n🎯 READY TO LOGIN!\n";
    echo "=================\n";
    echo "Your server is running. Try these steps:\n\n";
    echo "1. Open browser and go to: {$baseUrl}/florida/login\n";
    echo "2. Login with: florida@test.com / password123\n";
    echo "3. You should be redirected to the Florida dashboard\n\n";
    
} else {
    echo "⚠️  Server returned HTTP {$httpCode}\n";
    echo "This might indicate a server configuration issue.\n";
}

echo "\n📋 If login still doesn't work:\n";
echo "1. Check browser developer tools for JavaScript errors\n";
echo "2. Look at Laravel logs: storage/logs/laravel.log\n";
echo "3. Verify database connection is working\n";
echo "4. Make sure all caches are cleared\n";

echo "\n🏁 Server test completed\n";