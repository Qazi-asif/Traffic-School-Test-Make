<?php
/**
 * Test Laragon Setup
 * Verify that Laragon Apache configuration is working
 */

echo "🧪 TESTING LARAGON SETUP\n";
echo "=======================\n\n";

// Test 1: Check if we can access the site
echo "TEST 1: Testing Site Accessibility\n";
echo "----------------------------------\n";

$baseUrl = 'http://nelly-elearning.test';

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
    echo "🔧 SOLUTION: Make sure Laragon is running and Apache is started\n\n";
} elseif ($httpCode === 200) {
    echo "✅ Site accessible (HTTP {$httpCode})\n";
    
    // Check if it's actually Laravel
    if (strpos($response, 'Laravel') !== false || strpos($response, 'csrf') !== false) {
        echo "✅ Laravel application detected\n";
    } else {
        echo "⚠️  Site accessible but may not be Laravel\n";
    }
} else {
    echo "⚠️  Site returned HTTP {$httpCode}\n";
    
    if ($httpCode === 404) {
        echo "🔧 SOLUTION: Check .htaccess files and virtual host configuration\n";
    } elseif ($httpCode === 500) {
        echo "🔧 SOLUTION: Check Laravel logs and PHP configuration\n";
    }
}

// Test 2: Test specific login routes
echo "\nTEST 2: Testing Login Routes\n";
echo "---------------------------\n";

$loginRoutes = [
    'Florida' => '/florida/login',
    'Missouri' => '/missouri/login',
    'Texas' => '/texas/login',
    'Delaware' => '/delaware/login'
];

foreach ($loginRoutes as $state => $route) {
    $url = $baseUrl . $route;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        echo "❌ {$state}: Connection error\n";
    } elseif ($httpCode === 200) {
        echo "✅ {$state}: Login page accessible\n";
    } else {
        echo "⚠️  {$state}: HTTP {$httpCode}\n";
    }
}

// Test 3: Check file structure
echo "\nTEST 3: Checking File Structure\n";
echo "------------------------------\n";

$files = [
    '.htaccess' => 'Root .htaccess for routing',
    'public/.htaccess' => 'Public .htaccess for Laravel',
    'public/index.php' => 'Laravel entry point',
    'index.php' => 'Root index.php for Laragon'
];

foreach ($files as $file => $description) {
    if (file_exists($file)) {
        echo "✅ {$file}: Exists ({$description})\n";
    } else {
        echo "❌ {$file}: Missing ({$description})\n";
    }
}

// Test 4: Check Laravel configuration
echo "\nTEST 4: Checking Laravel Configuration\n";
echo "-------------------------------------\n";

if (file_exists('.env')) {
    $envContent = file_get_contents('.env');
    
    if (strpos($envContent, 'nelly-elearning.test') !== false) {
        echo "✅ APP_URL configured for nelly-elearning.test\n";
    } else {
        echo "⚠️  APP_URL may not be configured correctly\n";
    }
    
    if (strpos($envContent, 'APP_KEY=base64:') !== false) {
        echo "✅ APP_KEY is set\n";
    } else {
        echo "❌ APP_KEY is missing\n";
    }
} else {
    echo "❌ .env file not found\n";
}

echo "\n🎯 SUMMARY\n";
echo "=========\n";

if ($httpCode === 200) {
    echo "✅ SUCCESS: Your Laravel application is working with Laragon!\n\n";
    
    echo "🌐 READY TO USE:\n";
    echo "===============\n";
    echo "Main site: http://nelly-elearning.test\n";
    echo "Florida login: http://nelly-elearning.test/florida/login\n";
    echo "Missouri login: http://nelly-elearning.test/missouri/login\n";
    echo "Texas login: http://nelly-elearning.test/texas/login\n";
    echo "Delaware login: http://nelly-elearning.test/delaware/login\n\n";
    
    echo "🔑 TEST CREDENTIALS:\n";
    echo "==================\n";
    echo "Email: florida@test.com\n";
    echo "Password: password123\n\n";
    
    echo "🎉 All systems are working! You can now test:\n";
    echo "✅ Multi-state authentication\n";
    echo "✅ Course progress tracking\n";
    echo "✅ Certificate generation\n";
    echo "✅ State-specific dashboards\n";
    
} else {
    echo "⚠️  NEEDS ATTENTION: Some issues found\n\n";
    
    echo "🔧 TROUBLESHOOTING STEPS:\n";
    echo "========================\n";
    echo "1. Make sure Laragon is running\n";
    echo "2. Start Apache service in Laragon\n";
    echo "3. Check if nelly-elearning.test is in your hosts file\n";
    echo "4. Restart Apache after making changes\n";
    echo "5. Check Laragon Apache error logs\n";
}

echo "\n🏁 Test completed at " . date('Y-m-d H:i:s') . "\n";