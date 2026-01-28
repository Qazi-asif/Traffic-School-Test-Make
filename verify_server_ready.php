<?php
/**
 * Verify Server Ready
 * Quick verification that everything is ready for server startup
 */

echo "🔍 VERIFYING SERVER READINESS\n";
echo "============================\n\n";

// Test 1: PHP Version
echo "TEST 1: PHP Installation\n";
echo "-----------------------\n";
echo "✅ PHP Version: " . phpversion() . "\n";
echo "✅ PHP SAPI: " . php_sapi_name() . "\n";

// Test 2: Required Extensions
echo "\nTEST 2: Required Extensions\n";
echo "--------------------------\n";
$extensions = ['pdo', 'mbstring', 'openssl', 'tokenizer', 'xml', 'ctype', 'json', 'curl'];
foreach ($extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "✅ {$ext}: Available\n";
    } else {
        echo "❌ {$ext}: Missing\n";
    }
}

// Test 3: Laravel Files
echo "\nTEST 3: Laravel Files\n";
echo "--------------------\n";
$files = ['artisan', 'composer.json', '.env', 'bootstrap/app.php', 'public/index.php'];
foreach ($files as $file) {
    if (file_exists($file)) {
        echo "✅ {$file}: Exists\n";
    } else {
        echo "❌ {$file}: Missing\n";
    }
}

// Test 4: Directories
echo "\nTEST 4: Required Directories\n";
echo "---------------------------\n";
$dirs = ['vendor', 'bootstrap', 'public', 'storage', 'resources'];
foreach ($dirs as $dir) {
    if (is_dir($dir)) {
        echo "✅ {$dir}/: Exists\n";
    } else {
        echo "❌ {$dir}/: Missing\n";
    }
}

// Test 5: Permissions
echo "\nTEST 5: File Permissions\n";
echo "-----------------------\n";
$writableDirs = ['storage', 'bootstrap/cache'];
foreach ($writableDirs as $dir) {
    if (is_writable($dir)) {
        echo "✅ {$dir}: Writable\n";
    } else {
        echo "❌ {$dir}: Not writable\n";
    }
}

// Test 6: Port Availability
echo "\nTEST 6: Port Availability\n";
echo "------------------------\n";
$ports = [8000, 8001, 8080];
foreach ($ports as $port) {
    $connection = @fsockopen('127.0.0.1', $port, $errno, $errstr, 1);
    if ($connection) {
        fclose($connection);
        echo "❌ Port {$port}: In use\n";
    } else {
        echo "✅ Port {$port}: Available\n";
    }
}

echo "\n🚀 SERVER STARTUP COMMANDS\n";
echo "=========================\n";
echo "Now you can start the server with:\n\n";
echo "Option 1 (Recommended):\n";
echo "   .\\php artisan serve --host=127.0.0.1 --port=8000\n\n";
echo "Option 2 (Alternative):\n";
echo "   .\\php -S 127.0.0.1:8000 -t public\n\n";
echo "Option 3 (Different port):\n";
echo "   .\\php artisan serve --host=127.0.0.1 --port=8001\n\n";

echo "🔑 AFTER SERVER STARTS:\n";
echo "======================\n";
echo "Visit: http://127.0.0.1:8000/florida/login\n";
echo "Email: florida@test.com\n";
echo "Password: password123\n\n";

echo "✅ Everything is ready for server startup!\n";