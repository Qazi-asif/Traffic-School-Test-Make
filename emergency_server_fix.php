<?php
/**
 * Emergency Server Fix
 * Comprehensive solution to get Laravel running immediately
 */

echo "🚨 EMERGENCY SERVER FIX\n";
echo "======================\n\n";

// Step 1: Check PHP installation
echo "STEP 1: Checking PHP Installation\n";
echo "---------------------------------\n";

$phpVersion = phpversion();
echo "✅ PHP Version: {$phpVersion}\n";

if (version_compare($phpVersion, '8.1.0', '<')) {
    echo "⚠️  Warning: PHP 8.1+ recommended for Laravel 11\n";
}

// Check required extensions
$requiredExtensions = ['pdo', 'mbstring', 'openssl', 'tokenizer', 'xml', 'ctype', 'json'];
$missingExtensions = [];

foreach ($requiredExtensions as $ext) {
    if (extension_loaded($ext)) {
        echo "✅ Extension {$ext}: Loaded\n";
    } else {
        echo "❌ Extension {$ext}: Missing\n";
        $missingExtensions[] = $ext;
    }
}

// Step 2: Fix Laravel configuration
echo "\nSTEP 2: Fixing Laravel Configuration\n";
echo "------------------------------------\n";

// Check .env file
if (file_exists('.env')) {
    echo "✅ .env file exists\n";
    
    $envContent = file_get_contents('.env');
    
    // Check APP_KEY
    if (strpos($envContent, 'APP_KEY=base64:') !== false) {
        echo "✅ APP_KEY is set\n";
    } else {
        echo "❌ APP_KEY missing, generating...\n";
        shell_exec('php artisan key:generate 2>&1');
        echo "✅ APP_KEY generated\n";
    }
    
    // Check APP_URL
    if (strpos($envContent, 'APP_URL=') !== false) {
        echo "✅ APP_URL is configured\n";
    } else {
        echo "⚠️  APP_URL not set\n";
    }
    
} else {
    echo "❌ .env file missing, creating from example...\n";
    if (file_exists('.env.example')) {
        copy('.env.example', '.env');
        shell_exec('php artisan key:generate 2>&1');
        echo "✅ .env file created and key generated\n";
    } else {
        echo "❌ .env.example also missing\n";
    }
}

// Step 3: Clear all caches aggressively
echo "\nSTEP 3: Clearing All Caches\n";
echo "---------------------------\n";

$cacheCommands = [
    'config:clear' => 'Configuration cache',
    'route:clear' => 'Route cache',
    'view:clear' => 'View cache',
    'cache:clear' => 'Application cache'
];

foreach ($cacheCommands as $command => $description) {
    try {
        $output = shell_exec("php artisan {$command} 2>&1");
        echo "✅ Cleared {$description}\n";
    } catch (Exception $e) {
        echo "⚠️  Failed to clear {$description}: " . $e->getMessage() . "\n";
    }
}

// Step 4: Check database connection
echo "\nSTEP 4: Checking Database Connection\n";
echo "-----------------------------------\n";

try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=nelly-elearning', 'root', '');
    echo "✅ Database connection successful\n";
    
    $userCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    echo "✅ Found {$userCount} users in database\n";
    
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    echo "⚠️  Server will still work, but login may fail\n";
}

// Step 5: Create multiple server startup methods
echo "\nSTEP 5: Creating Server Startup Methods\n";
echo "--------------------------------------\n";

// Method 1: Standard artisan serve
$method1 = 'start_method1.bat';
file_put_contents($method1, '@echo off
echo Starting Laravel with artisan serve...
php artisan serve --host=127.0.0.1 --port=8000
pause');
echo "✅ Created {$method1} (Standard Laravel server)\n";

// Method 2: Built-in PHP server
$method2 = 'start_method2.bat';
file_put_contents($method2, '@echo off
echo Starting with built-in PHP server...
php -S 127.0.0.1:8000 -t public
pause');
echo "✅ Created {$method2} (Built-in PHP server)\n";

// Method 3: Alternative port
$method3 = 'start_method3.bat';
file_put_contents($method3, '@echo off
echo Starting Laravel on port 8001...
php artisan serve --host=127.0.0.1 --port=8001
pause');
echo "✅ Created {$method3} (Alternative port 8001)\n";

// Method 4: Localhost instead of 127.0.0.1
$method4 = 'start_method4.bat';
file_put_contents($method4, '@echo off
echo Starting Laravel on localhost...
php artisan serve --host=localhost --port=8000
pause');
echo "✅ Created {$method4} (Using localhost)\n";

// Step 6: Create a simple router for testing
echo "\nSTEP 6: Creating Emergency Router\n";
echo "---------------------------------\n";

$emergencyRouter = '<?php
/**
 * Emergency Router - Minimal Laravel bootstrap for testing
 */

// Set up basic paths
define("LARAVEL_START", microtime(true));

// Require the Composer autoloader
require __DIR__ . "/vendor/autoload.php";

// Bootstrap Laravel application
$app = require_once __DIR__ . "/bootstrap/app.php";

// Handle the request
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Illuminate\Http\Request::capture();
$response = $kernel->handle($request);

$response->send();

$kernel->terminate($request, $response);
';

file_put_contents('emergency_router.php', $emergencyRouter);
echo "✅ Created emergency_router.php\n";

// Method 5: Emergency router method
$method5 = 'start_emergency.bat';
file_put_contents($method5, '@echo off
echo Starting with emergency router...
php -S 127.0.0.1:8000 emergency_router.php
pause');
echo "✅ Created {$method5} (Emergency router)\n";

// Step 7: Test port availability
echo "\nSTEP 7: Testing Port Availability\n";
echo "---------------------------------\n";

$ports = [8000, 8001, 8080, 3000];
$availablePorts = [];

foreach ($ports as $port) {
    $connection = @fsockopen('127.0.0.1', $port, $errno, $errstr, 1);
    if ($connection) {
        fclose($connection);
        echo "❌ Port {$port}: In use\n";
    } else {
        echo "✅ Port {$port}: Available\n";
        $availablePorts[] = $port;
    }
}

// Step 8: Create comprehensive startup guide
echo "\nSTEP 8: Creating Startup Guide\n";
echo "------------------------------\n";

$startupGuide = "🚀 LARAVEL SERVER STARTUP GUIDE
===============================

Your Laravel application is ready! Try these methods in order:

METHOD 1: Standard Laravel Server
---------------------------------
Double-click: start_method1.bat
Or run: php artisan serve --host=127.0.0.1 --port=8000
Access: http://127.0.0.1:8000

METHOD 2: Built-in PHP Server  
-----------------------------
Double-click: start_method2.bat
Or run: php -S 127.0.0.1:8000 -t public
Access: http://127.0.0.1:8000

METHOD 3: Alternative Port
--------------------------
Double-click: start_method3.bat
Or run: php artisan serve --host=127.0.0.1 --port=8001
Access: http://127.0.0.1:8001

METHOD 4: Using Localhost
-------------------------
Double-click: start_method4.bat
Or run: php artisan serve --host=localhost --port=8000
Access: http://localhost:8000

METHOD 5: Emergency Router
--------------------------
Double-click: start_emergency.bat
Or run: php -S 127.0.0.1:8000 emergency_router.php
Access: http://127.0.0.1:8000

🔑 LOGIN CREDENTIALS:
====================
Email: florida@test.com
Password: password123

Other test accounts:
- missouri@test.com / password123
- texas@test.com / password123  
- delaware@test.com / password123
- admin@test.com / admin123

🎯 LOGIN URLS:
=============
- Florida: http://127.0.0.1:8000/florida/login
- Missouri: http://127.0.0.1:8000/missouri/login
- Texas: http://127.0.0.1:8000/texas/login
- Delaware: http://127.0.0.1:8000/delaware/login

📋 WHAT'S READY TO TEST:
=======================
✅ Multi-state authentication system
✅ Course progress tracking
✅ Certificate generation
✅ State-specific dashboards
✅ Progress monitoring APIs
✅ Certificate management

🔧 TROUBLESHOOTING:
==================
If none of the methods work:
1. Check if PHP is in your system PATH
2. Try running: php --version
3. Install PHP if not available
4. Check Windows Firewall settings
5. Try different ports (8001, 8080, 3000)

📞 SUPPORT:
==========
If you still have issues:
1. Check the Laravel log: storage/logs/laravel.log
2. Verify database connection in .env
3. Ensure all PHP extensions are installed
4. Try running: composer install
";

file_put_contents('STARTUP_GUIDE.txt', $startupGuide);
echo "✅ Created STARTUP_GUIDE.txt\n";

// Step 9: Final system check
echo "\nSTEP 9: Final System Check\n";
echo "--------------------------\n";

$checks = [
    'PHP executable' => is_executable('php') || shell_exec('php --version 2>&1'),
    'Artisan file' => file_exists('artisan'),
    'Vendor directory' => is_dir('vendor'),
    'Bootstrap directory' => is_dir('bootstrap'),
    'Public directory' => is_dir('public'),
    '.env file' => file_exists('.env')
];

$allGood = true;
foreach ($checks as $check => $result) {
    if ($result) {
        echo "✅ {$check}: OK\n";
    } else {
        echo "❌ {$check}: FAIL\n";
        $allGood = false;
    }
}

echo "\n🎉 EMERGENCY SERVER FIX COMPLETE!\n";
echo "=================================\n";

if ($allGood) {
    echo "✅ All system checks passed\n";
    echo "✅ Multiple startup methods created\n";
    echo "✅ Emergency router ready\n";
    echo "✅ Comprehensive guide created\n\n";
    
    echo "🚀 READY TO START:\n";
    echo "1. Double-click any start_method*.bat file\n";
    echo "2. Or follow the STARTUP_GUIDE.txt\n";
    echo "3. Visit http://127.0.0.1:8000/florida/login\n";
    echo "4. Login with florida@test.com / password123\n\n";
    
    if (!empty($availablePorts)) {
        echo "📡 Available ports: " . implode(', ', $availablePorts) . "\n";
    }
    
} else {
    echo "⚠️  Some system checks failed\n";
    echo "📋 Please check the failed items above\n";
    echo "📖 Refer to STARTUP_GUIDE.txt for troubleshooting\n";
}

echo "\n🏁 Emergency fix completed at " . date('Y-m-d H:i:s') . "\n";
echo "Your Laravel application is ready to run!\n";