<?php
/**
 * Final Login Readiness Check
 * Complete verification that the login system is ready to use
 */

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\User;

echo "🔍 FINAL LOGIN READINESS CHECK\n";
echo "=============================\n\n";

$allGood = true;

try {
    // Check 1: Database Connection
    echo "✓ Database Connection\n";
    echo "--------------------\n";
    $userCount = DB::table('users')->count();
    echo "✅ Connected - {$userCount} users found\n\n";
    
    // Check 2: Test Users
    echo "✓ Test Users\n";
    echo "-----------\n";
    $testUsers = [
        'florida@test.com' => 'password123',
        'missouri@test.com' => 'password123',
        'texas@test.com' => 'password123',
        'delaware@test.com' => 'password123',
        'admin@test.com' => 'admin123'
    ];
    
    foreach ($testUsers as $email => $password) {
        $user = User::where('email', $email)->first();
        if ($user) {
            $passwordCheck = \Illuminate\Support\Facades\Hash::check($password, $user->password);
            if ($passwordCheck) {
                echo "✅ {$email} - Password OK\n";
            } else {
                echo "❌ {$email} - Password FAIL\n";
                $allGood = false;
            }
        } else {
            echo "❌ {$email} - User NOT FOUND\n";
            $allGood = false;
        }
    }
    
    // Check 3: Controllers
    echo "\n✓ Controllers\n";
    echo "------------\n";
    if (class_exists('App\Http\Controllers\Auth\StateAuthController')) {
        echo "✅ StateAuthController - Available\n";
    } else {
        echo "❌ StateAuthController - Missing\n";
        $allGood = false;
    }
    
    // Check 4: Views
    echo "\n✓ Views\n";
    echo "------\n";
    $requiredViews = [
        'resources/views/auth/state-login.blade.php' => 'Login Form',
        'resources/views/student/florida/dashboard.blade.php' => 'Florida Dashboard',
        'resources/views/student/missouri/dashboard.blade.php' => 'Missouri Dashboard',
        'resources/views/student/texas/dashboard.blade.php' => 'Texas Dashboard',
        'resources/views/student/delaware/dashboard.blade.php' => 'Delaware Dashboard'
    ];
    
    foreach ($requiredViews as $path => $name) {
        if (file_exists($path)) {
            echo "✅ {$name} - Available\n";
        } else {
            echo "❌ {$name} - Missing\n";
            $allGood = false;
        }
    }
    
    // Check 5: Routes
    echo "\n✓ Routes\n";
    echo "-------\n";
    $routesContent = file_get_contents('routes/web.php');
    if (strpos($routesContent, 'StateAuthController') !== false) {
        echo "✅ Authentication routes - Configured\n";
    } else {
        echo "❌ Authentication routes - Missing\n";
        $allGood = false;
    }
    
    // Check 6: Server Accessibility
    echo "\n✓ Server Accessibility\n";
    echo "---------------------\n";
    
    $testUrl = 'http://nelly-elearning.test/florida-simple';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $testUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        echo "❌ Server not accessible - {$error}\n";
        $allGood = false;
    } elseif ($httpCode === 200) {
        echo "✅ Server accessible - HTTP {$httpCode}\n";
    } else {
        echo "⚠️  Server returned HTTP {$httpCode}\n";
    }
    
    // Final Assessment
    echo "\n" . str_repeat("=", 50) . "\n";
    
    if ($allGood) {
        echo "🎉 LOGIN SYSTEM IS READY!\n";
        echo "========================\n\n";
        
        echo "🔑 LOGIN INSTRUCTIONS:\n";
        echo "1. Open your web browser\n";
        echo "2. Go to: http://nelly-elearning.test/florida/login\n";
        echo "3. Enter email: florida@test.com\n";
        echo "4. Enter password: password123\n";
        echo "5. Click 'Login to Florida Portal'\n";
        echo "6. You should be redirected to the Florida dashboard\n\n";
        
        echo "🌐 ALL STATE LOGIN URLS:\n";
        echo "Florida: http://nelly-elearning.test/florida/login\n";
        echo "Missouri: http://nelly-elearning.test/missouri/login\n";
        echo "Texas: http://nelly-elearning.test/texas/login\n";
        echo "Delaware: http://nelly-elearning.test/delaware/login\n\n";
        
        echo "👤 TEST ACCOUNTS:\n";
        foreach ($testUsers as $email => $password) {
            echo "{$email} / {$password}\n";
        }
        
    } else {
        echo "❌ LOGIN SYSTEM HAS ISSUES\n";
        echo "=========================\n";
        echo "Please review the failed checks above and fix them before attempting to login.\n";
    }
    
} catch (Exception $e) {
    echo "❌ CRITICAL ERROR: " . $e->getMessage() . "\n";
    $allGood = false;
}

echo "\n🏁 Readiness check completed at " . date('Y-m-d H:i:s') . "\n";

if ($allGood) {
    echo "\n✅ You should now be able to login and test the progress system!\n";
} else {
    echo "\n❌ Please fix the issues above before attempting to login.\n";
}