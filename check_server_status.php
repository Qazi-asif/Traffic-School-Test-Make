<?php
/**
 * Check Server Status - Real-time server check
 */

echo "🔍 Checking Laravel Server Status...\n";
echo "===================================\n\n";

$maxAttempts = 10;
$attempt = 0;
$serverRunning = false;

while ($attempt < $maxAttempts && !$serverRunning) {
    $attempt++;
    echo "Attempt {$attempt}/{$maxAttempts}: ";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://127.0.0.1:8000');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 2);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($httpCode === 200) {
        echo "✅ Server is running!\n";
        $serverRunning = true;
        break;
    } else {
        echo "⏳ Waiting... (Error: " . ($error ?: "HTTP {$httpCode}") . ")\n";
        sleep(2);
    }
}

if ($serverRunning) {
    echo "\n🎉 SUCCESS! Laravel server is running on http://127.0.0.1:8000\n\n";
    
    echo "🔑 LOGIN URLS:\n";
    echo "- Florida: http://127.0.0.1:8000/florida/login\n";
    echo "- Missouri: http://127.0.0.1:8000/missouri/login\n";
    echo "- Texas: http://127.0.0.1:8000/texas/login\n";
    echo "- Delaware: http://127.0.0.1:8000/delaware/login\n\n";
    
    echo "👤 TEST CREDENTIALS:\n";
    echo "Email: florida@test.com\n";
    echo "Password: password123\n\n";
    
    echo "📋 READY TO TEST:\n";
    echo "✅ Multi-state authentication\n";
    echo "✅ Course progress tracking\n";
    echo "✅ Certificate generation\n";
    echo "✅ State-specific dashboards\n\n";
    
    // Test login pages
    echo "🧪 Testing login pages:\n";
    $states = ['florida', 'missouri', 'texas', 'delaware'];
    
    foreach ($states as $state) {
        $url = "http://127.0.0.1:8000/{$state}/login";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            echo "✅ {$state} login: Working\n";
        } else {
            echo "❌ {$state} login: HTTP {$httpCode}\n";
        }
    }
    
} else {
    echo "\n❌ Server failed to start after {$maxAttempts} attempts\n";
    echo "\n🔧 Manual startup required:\n";
    echo "1. Open a new terminal/command prompt\n";
    echo "2. Navigate to: D:\\laragon\\www\\nelly-elearning\n";
    echo "3. Run: php artisan serve --host=127.0.0.1 --port=8000\n";
    echo "4. Keep that terminal open\n";
    echo "5. Visit: http://127.0.0.1:8000/florida/login\n";
}

echo "\n🏁 Server check completed\n";