<?php
/**
 * Test State Auth Controller
 * Verify the StateAuthController is working properly
 */

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🧪 Testing State Auth Controller\n";
echo "===============================\n\n";

try {
    // Test 1: Check if controller exists
    echo "TEST 1: Controller Existence\n";
    echo "---------------------------\n";
    
    if (class_exists('App\Http\Controllers\Auth\StateAuthController')) {
        echo "✅ StateAuthController class exists\n";
        
        $controller = new App\Http\Controllers\Auth\StateAuthController();
        echo "✅ Controller can be instantiated\n";
        
        // Check if methods exist
        $methods = ['showLoginForm', 'login', 'showRegistrationForm', 'register', 'logout'];
        foreach ($methods as $method) {
            if (method_exists($controller, $method)) {
                echo "✅ Method '{$method}' exists\n";
            } else {
                echo "❌ Method '{$method}' missing\n";
            }
        }
    } else {
        echo "❌ StateAuthController class not found\n";
    }
    
    // Test 2: Check views
    echo "\nTEST 2: Authentication Views\n";
    echo "---------------------------\n";
    
    $views = [
        'resources/views/auth/state-login.blade.php',
        'resources/views/auth/state-register.blade.php'
    ];
    
    foreach ($views as $view) {
        if (file_exists($view)) {
            echo "✅ View exists: " . basename($view) . "\n";
        } else {
            echo "❌ View missing: " . basename($view) . "\n";
        }
    }
    
    // Test 3: Check dashboard views
    echo "\nTEST 3: Dashboard Views\n";
    echo "----------------------\n";
    
    $states = ['florida', 'missouri', 'texas', 'delaware'];
    foreach ($states as $state) {
        $dashboardPath = "resources/views/student/{$state}/dashboard.blade.php";
        if (file_exists($dashboardPath)) {
            echo "✅ {$state} dashboard exists\n";
        } else {
            echo "❌ {$state} dashboard missing\n";
        }
    }
    
    // Test 4: Test database users
    echo "\nTEST 4: Test Users\n";
    echo "-----------------\n";
    
    $testEmails = ['florida@test.com', 'missouri@test.com', 'texas@test.com', 'delaware@test.com'];
    foreach ($testEmails as $email) {
        $user = \App\Models\User::where('email', $email)->first();
        if ($user) {
            echo "✅ User exists: {$email} (State: {$user->state})\n";
        } else {
            echo "❌ User missing: {$email}\n";
        }
    }
    
    // Test 5: Create a simple login test
    echo "\nTEST 5: Simple Login Test\n";
    echo "------------------------\n";
    
    // Test authentication logic
    $testUser = \App\Models\User::where('email', 'florida@test.com')->first();
    if ($testUser) {
        $passwordCheck = \Illuminate\Support\Facades\Hash::check('password123', $testUser->password);
        echo "✅ Test user password verification: " . ($passwordCheck ? 'PASS' : 'FAIL') . "\n";
        
        // Test if user can be authenticated
        if ($passwordCheck) {
            echo "✅ Login credentials are valid\n";
        } else {
            echo "❌ Login credentials are invalid\n";
        }
    }
    
    // Test 6: Route accessibility test
    echo "\nTEST 6: Route Accessibility\n";
    echo "--------------------------\n";
    
    // Create a simple HTTP test
    $testUrls = [
        'http://nelly-elearning.test/florida/login',
        'http://nelly-elearning.test/missouri/login',
        'http://nelly-elearning.test/texas/login',
        'http://nelly-elearning.test/delaware/login'
    ];
    
    foreach ($testUrls as $url) {
        $state = explode('/', parse_url($url, PHP_URL_PATH))[1];
        echo "Testing {$state} login URL...\n";
        
        // Use curl to test
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HEADER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            echo "❌ {$state}: Connection error - {$error}\n";
        } elseif ($httpCode === 200) {
            echo "✅ {$state}: Accessible (HTTP {$httpCode})\n";
        } else {
            echo "⚠️  {$state}: HTTP {$httpCode}\n";
        }
    }
    
    echo "\n🎯 SUMMARY\n";
    echo "=========\n";
    echo "If all tests above show ✅, your login system should be working.\n";
    echo "If you see ❌, there are issues that need to be fixed.\n\n";
    
    echo "🔑 LOGIN INSTRUCTIONS:\n";
    echo "=====================\n";
    echo "1. Open your browser\n";
    echo "2. Go to: http://nelly-elearning.test/florida/login\n";
    echo "3. Enter: florida@test.com\n";
    echo "4. Password: password123\n";
    echo "5. Click Login\n\n";
    
    echo "If login doesn't work, check:\n";
    echo "- Is your Laravel server running?\n";
    echo "- Are there any error messages in the browser?\n";
    echo "- Check Laravel logs for errors\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n🏁 Test completed at " . date('Y-m-d H:i:s') . "\n";