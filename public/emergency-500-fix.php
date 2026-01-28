<?php
// Emergency 500 Error Fix - Simple and Safe
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Emergency 500 Error Fix</h1>";
echo "<pre>";

try {
    echo "=== EMERGENCY 500 ERROR FIX ===\n\n";
    
    // 1. Basic PHP and environment check
    echo "1. Basic environment check...\n";
    echo "   PHP Version: " . PHP_VERSION . "\n";
    echo "   Current directory: " . getcwd() . "\n";
    echo "   Script path: " . __FILE__ . "\n";
    
    // 2. Check if Laravel can be loaded
    echo "\n2. Checking Laravel bootstrap...\n";
    
    $vendorPath = '../vendor/autoload.php';
    if (!file_exists($vendorPath)) {
        echo "   ❌ Composer autoload not found at: {$vendorPath}\n";
        echo "   Run: composer install\n";
        exit;
    }
    
    require_once $vendorPath;
    echo "   ✅ Composer autoload loaded\n";
    
    $bootstrapPath = '../bootstrap/app.php';
    if (!file_exists($bootstrapPath)) {
        echo "   ❌ Laravel bootstrap not found at: {$bootstrapPath}\n";
        exit;
    }
    
    $app = require_once $bootstrapPath;
    echo "   ✅ Laravel app loaded\n";
    
    // 3. Try to bootstrap Laravel kernel
    try {
        $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
        echo "   ✅ Laravel kernel bootstrapped\n";
    } catch (Exception $e) {
        echo "   ❌ Laravel kernel bootstrap failed: " . $e->getMessage() . "\n";
        echo "   This might be a configuration issue\n";
    }
    
    // 4. Check database connection
    echo "\n3. Checking database connection...\n";
    
    try {
        $pdo = \Illuminate\Support\Facades\DB::connection()->getPdo();
        echo "   ✅ Database connection successful\n";
        echo "   Database: " . $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) . "\n";
    } catch (Exception $e) {
        echo "   ❌ Database connection failed: " . $e->getMessage() . "\n";
        echo "   Check your .env database settings\n";
    }
    
    // 5. Simple table check
    echo "\n4. Checking essential tables...\n";
    
    $essentialTables = ['users', 'florida_courses'];
    
    foreach ($essentialTables as $table) {
        try {
            $exists = \Illuminate\Support\Facades\Schema::hasTable($table);
            if ($exists) {
                $count = \Illuminate\Support\Facades\DB::table($table)->count();
                echo "   ✅ {$table} table exists with {$count} records\n";
            } else {
                echo "   ❌ {$table} table missing\n";
            }
        } catch (Exception $e) {
            echo "   ❌ Error checking {$table}: " . $e->getMessage() . "\n";
        }
    }
    
    // 6. Check if users have roles
    echo "\n5. Checking user roles...\n";
    
    try {
        $hasRoleColumn = \Illuminate\Support\Facades\Schema::hasColumn('users', 'role');
        if ($hasRoleColumn) {
            echo "   ✅ Users table has role column\n";
            
            $adminCount = \Illuminate\Support\Facades\DB::table('users')
                ->whereIn('role', ['admin', 'super-admin'])
                ->count();
            echo "   Admin users: {$adminCount}\n";
            
            if ($adminCount === 0) {
                echo "   ⚠️ No admin users found\n";
            }
        } else {
            echo "   ❌ Users table missing role column\n";
        }
    } catch (Exception $e) {
        echo "   ❌ Error checking user roles: " . $e->getMessage() . "\n";
    }
    
    // 7. Test a simple controller
    echo "\n6. Testing basic controller...\n";
    
    try {
        $controller = new \App\Http\Controllers\CourseController();
        echo "   ✅ CourseController can be instantiated\n";
        
        // Test a simple method
        $request = new \Illuminate\Http\Request();
        $response = $controller->indexWeb($request);
        echo "   ✅ CourseController indexWeb method works\n";
        echo "   Response status: " . $response->getStatusCode() . "\n";
        
    } catch (Exception $e) {
        echo "   ❌ Controller test failed: " . $e->getMessage() . "\n";
        echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    }
    
    // 8. Check Laravel logs
    echo "\n7. Checking Laravel logs...\n";
    
    $logPath = storage_path('logs/laravel.log');
    if (file_exists($logPath)) {
        echo "   ✅ Laravel log file exists\n";
        echo "   Size: " . number_format(filesize($logPath)) . " bytes\n";
        
        // Get last few lines of log
        $logLines = file($logPath);
        if ($logLines) {
            $lastLines = array_slice($logLines, -10);
            echo "   Last 10 log entries:\n";
            foreach ($lastLines as $line) {
                echo "   " . trim($line) . "\n";
            }
        }
    } else {
        echo "   ⚠️ No Laravel log file found\n";
    }
    
    // 9. Create a simple test endpoint
    echo "\n8. Creating simple test endpoint...\n";
    
    $simpleTest = '<?php
// Simple Test Endpoint
header("Content-Type: application/json");

try {
    echo json_encode([
        "status" => "success",
        "message" => "Simple test endpoint works",
        "timestamp" => date("Y-m-d H:i:s"),
        "php_version" => PHP_VERSION
    ]);
} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}
?>';
    
    file_put_contents(public_path('simple-test.php'), $simpleTest);
    echo "   ✅ Created simple test endpoint\n";
    echo "   Test at: http://nelly-elearning.test/simple-test.php\n";
    
    echo "\n🎯 DIAGNOSIS COMPLETE!\n";
    
    echo "\n📝 NEXT STEPS:\n";
    echo "1. Check the Laravel log entries above for specific errors\n";
    echo "2. Test the simple endpoint: http://nelly-elearning.test/simple-test.php\n";
    echo "3. If database connection failed, check .env file\n";
    echo "4. If controller test failed, there might be a code issue\n";
    
    echo "\n💡 COMMON 500 ERROR CAUSES:\n";
    echo "1. Database connection issues (.env configuration)\n";
    echo "2. Missing database tables or columns\n";
    echo "3. PHP syntax errors in controllers\n";
    echo "4. Missing dependencies (composer install)\n";
    echo "5. File permission issues\n";
    echo "6. Memory limit exceeded\n";
    
} catch (Exception $e) {
    echo "❌ EMERGENCY FIX ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "</pre>";
?>