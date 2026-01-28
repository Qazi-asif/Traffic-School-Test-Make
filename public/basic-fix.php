<?php
// Basic Fix - No Laravel Dependencies
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Basic System Fix</h1>";
echo "<pre>";

echo "=== BASIC SYSTEM FIX (NO LARAVEL) ===\n\n";

// 1. Check PHP environment
echo "1. PHP Environment Check...\n";
echo "   PHP Version: " . PHP_VERSION . "\n";
echo "   Memory Limit: " . ini_get('memory_limit') . "\n";
echo "   Max Execution Time: " . ini_get('max_execution_time') . "\n";
echo "   Upload Max Filesize: " . ini_get('upload_max_filesize') . "\n";
echo "   Post Max Size: " . ini_get('post_max_size') . "\n";

// 2. Check file permissions
echo "\n2. File Permissions Check...\n";

$directories = [
    '../storage',
    '../storage/app',
    '../storage/logs',
    '../storage/framework',
    '../bootstrap/cache'
];

foreach ($directories as $dir) {
    if (is_dir($dir)) {
        $writable = is_writable($dir);
        echo "   {$dir}: " . ($writable ? "✅ Writable" : "❌ Not writable") . "\n";
        
        if (!$writable) {
            echo "     Try: chmod 755 {$dir}\n";
        }
    } else {
        echo "   {$dir}: ❌ Directory doesn't exist\n";
        echo "     Try: mkdir -p {$dir}\n";
    }
}

// 3. Check .env file
echo "\n3. Environment File Check...\n";

$envFile = '../.env';
if (file_exists($envFile)) {
    echo "   ✅ .env file exists\n";
    
    $envContent = file_get_contents($envFile);
    
    // Check for essential settings
    $essentialSettings = [
        'APP_KEY' => 'Application encryption key',
        'DB_CONNECTION' => 'Database connection type',
        'DB_HOST' => 'Database host',
        'DB_DATABASE' => 'Database name',
        'DB_USERNAME' => 'Database username'
    ];
    
    foreach ($essentialSettings as $setting => $description) {
        if (strpos($envContent, $setting . '=') !== false) {
            echo "   ✅ {$setting} is set - {$description}\n";
        } else {
            echo "   ❌ {$setting} missing - {$description}\n";
        }
    }
} else {
    echo "   ❌ .env file missing\n";
    echo "   Copy .env.example to .env and configure it\n";
}

// 4. Check composer dependencies
echo "\n4. Composer Dependencies Check...\n";

$vendorDir = '../vendor';
if (is_dir($vendorDir)) {
    echo "   ✅ Vendor directory exists\n";
    
    $autoloadFile = $vendorDir . '/autoload.php';
    if (file_exists($autoloadFile)) {
        echo "   ✅ Composer autoload file exists\n";
    } else {
        echo "   ❌ Composer autoload file missing\n";
        echo "   Run: composer install\n";
    }
} else {
    echo "   ❌ Vendor directory missing\n";
    echo "   Run: composer install\n";
}

// 5. Check Laravel files
echo "\n5. Laravel Files Check...\n";

$laravelFiles = [
    '../artisan' => 'Laravel Artisan command',
    '../bootstrap/app.php' => 'Laravel bootstrap file',
    '../app/Http/Kernel.php' => 'HTTP Kernel',
    '../config/app.php' => 'App configuration'
];

foreach ($laravelFiles as $file => $description) {
    if (file_exists($file)) {
        echo "   ✅ {$description} exists\n";
    } else {
        echo "   ❌ {$description} missing: {$file}\n";
    }
}

// 6. Try basic database connection (if possible)
echo "\n6. Database Connection Test...\n";

try {
    // Try to load .env manually
    if (file_exists('../.env')) {
        $envLines = file('../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $envVars = [];
        
        foreach ($envLines as $line) {
            if (strpos($line, '=') !== false && !str_starts_with($line, '#')) {
                list($key, $value) = explode('=', $line, 2);
                $envVars[trim($key)] = trim($value, '"\'');
            }
        }
        
        if (isset($envVars['DB_CONNECTION']) && $envVars['DB_CONNECTION'] === 'mysql') {
            $host = $envVars['DB_HOST'] ?? 'localhost';
            $database = $envVars['DB_DATABASE'] ?? '';
            $username = $envVars['DB_USERNAME'] ?? '';
            $password = $envVars['DB_PASSWORD'] ?? '';
            
            if ($database && $username) {
                try {
                    $pdo = new PDO("mysql:host={$host};dbname={$database}", $username, $password);
                    echo "   ✅ Database connection successful\n";
                    echo "   Database: {$database} on {$host}\n";
                    
                    // Check if essential tables exist
                    $tables = ['users', 'florida_courses'];
                    foreach ($tables as $table) {
                        $stmt = $pdo->query("SHOW TABLES LIKE '{$table}'");
                        if ($stmt->rowCount() > 0) {
                            echo "   ✅ Table '{$table}' exists\n";
                        } else {
                            echo "   ❌ Table '{$table}' missing\n";
                        }
                    }
                    
                } catch (PDOException $e) {
                    echo "   ❌ Database connection failed: " . $e->getMessage() . "\n";
                }
            } else {
                echo "   ⚠️ Database credentials not complete in .env\n";
            }
        } else {
            echo "   ⚠️ Database not configured or not MySQL\n";
        }
    }
} catch (Exception $e) {
    echo "   ❌ Database test error: " . $e->getMessage() . "\n";
}

// 7. Create a working test page
echo "\n7. Creating Test Pages...\n";

// Simple PHP info page
$phpInfoPage = '<?php
// PHP Info Test
phpinfo();
?>';

file_put_contents('php-info.php', $phpInfoPage);
echo "   ✅ Created PHP info page: http://nelly-elearning.test/php-info.php\n";

// Simple JSON test
$jsonTestPage = '<?php
header("Content-Type: application/json");

$response = [
    "status" => "success",
    "message" => "Basic JSON test works",
    "timestamp" => date("Y-m-d H:i:s"),
    "server_info" => [
        "php_version" => PHP_VERSION,
        "server_software" => $_SERVER["SERVER_SOFTWARE"] ?? "unknown"
    ]
];

echo json_encode($response, JSON_PRETTY_PRINT);
?>';

file_put_contents('json-test.php', $jsonTestPage);
echo "   ✅ Created JSON test page: http://nelly-elearning.test/json-test.php\n";

echo "\n🎯 BASIC FIX COMPLETE!\n";

echo "\n📝 TEST THESE PAGES:\n";
echo "1. PHP Info: http://nelly-elearning.test/php-info.php\n";
echo "2. JSON Test: http://nelly-elearning.test/json-test.php\n";

echo "\n💡 IF THESE WORK:\n";
echo "- Your server and PHP are working fine\n";
echo "- The issue is with Laravel configuration\n";
echo "- Check the Laravel log files\n";
echo "- Run: composer install\n";
echo "- Check database configuration in .env\n";

echo "\n💡 IF THESE DON'T WORK:\n";
echo "- There's a server-level issue\n";
echo "- Check Apache/Nginx configuration\n";
echo "- Check file permissions\n";
echo "- Check PHP error logs\n";

echo "</pre>";
?>