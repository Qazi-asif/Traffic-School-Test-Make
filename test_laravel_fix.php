<?php
/**
 * Test Laravel Fix - Diagnose and Fix Laravel Issues
 */

echo "🔧 TESTING LARAVEL FIX\n";
echo "======================\n\n";

// Step 1: Test basic PHP
echo "1. TESTING BASIC PHP:\n";
echo "=====================\n";
echo "✅ PHP Version: " . PHP_VERSION . "\n";
echo "✅ Current Directory: " . __DIR__ . "\n";
echo "✅ Memory Limit: " . ini_get('memory_limit') . "\n";

// Step 2: Test Composer Autoload
echo "\n2. TESTING COMPOSER AUTOLOAD:\n";
echo "==============================\n";

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    echo "✅ Composer autoload file exists\n";
    
    try {
        require_once __DIR__ . '/vendor/autoload.php';
        echo "✅ Composer autoload loaded successfully\n";
        
        // Test if Laravel classes exist
        if (class_exists('Illuminate\Foundation\Application')) {
            echo "✅ Laravel Application class exists\n";
        } else {
            echo "❌ Laravel Application class missing\n";
        }
        
        if (class_exists('Illuminate\View\ViewServiceProvider')) {
            echo "✅ ViewServiceProvider class exists\n";
        } else {
            echo "❌ ViewServiceProvider class missing\n";
        }
        
    } catch (Exception $e) {
        echo "❌ Composer autoload failed: " . $e->getMessage() . "\n";
        exit(1);
    }
} else {
    echo "❌ Composer autoload not found\n";
    exit(1);
}

// Step 3: Test Laravel Bootstrap
echo "\n3. TESTING LARAVEL BOOTSTRAP:\n";
echo "==============================\n";

if (file_exists(__DIR__ . '/bootstrap/app.php')) {
    echo "✅ Laravel bootstrap file exists\n";
    
    try {
        $app = require __DIR__ . '/bootstrap/app.php';
        echo "✅ Laravel application created\n";
        echo "✅ Laravel version: " . $app->version() . "\n";
        
        // Test service container
        try {
            $container = $app->getContainer();
            echo "✅ Service container accessible\n";
        } catch (Exception $e) {
            echo "❌ Service container error: " . $e->getMessage() . "\n";
        }
        
        // Test if we can bootstrap the kernel
        try {
            $kernel = $app->make('Illuminate\Contracts\Console\Kernel');
            echo "✅ Console kernel created\n";
            
            $kernel->bootstrap();
            echo "✅ Laravel kernel bootstrapped\n";
            
            // Test view service
            try {
                $view = app('view');
                echo "✅ View service available: " . get_class($view) . "\n";
            } catch (Exception $e) {
                echo "❌ View service error: " . $e->getMessage() . "\n";
                
                // Try to register ViewServiceProvider manually
                try {
                    $app->register('Illuminate\View\ViewServiceProvider');
                    echo "✅ ViewServiceProvider registered manually\n";
                    
                    $view = app('view');
                    echo "✅ View service now available: " . get_class($view) . "\n";
                } catch (Exception $e2) {
                    echo "❌ Manual ViewServiceProvider registration failed: " . $e2->getMessage() . "\n";
                }
            }
            
        } catch (Exception $e) {
            echo "❌ Kernel bootstrap failed: " . $e->getMessage() . "\n";
        }
        
    } catch (Exception $e) {
        echo "❌ Laravel bootstrap failed: " . $e->getMessage() . "\n";
    }
} else {
    echo "❌ Laravel bootstrap file not found\n";
}

// Step 4: Test Database Connection
echo "\n4. TESTING DATABASE CONNECTION:\n";
echo "================================\n";

try {
    if (function_exists('app')) {
        $db = app('db');
        $pdo = $db->connection()->getPdo();
        echo "✅ Database connection successful\n";
        echo "✅ Database driver: " . $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) . "\n";
    } else {
        echo "❌ Laravel app() helper not available\n";
    }
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
}

echo "\n🎯 LARAVEL DIAGNOSIS COMPLETE\n";
echo "==============================\n";

if (function_exists('app')) {
    try {
        $view = app('view');
        echo "✅ LARAVEL IS WORKING! View service available.\n";
        echo "✅ You can now test: http://nelly-elearning.test/florida.php\n";
    } catch (Exception $e) {
        echo "❌ Laravel partially working but view service still broken\n";
        echo "   Error: " . $e->getMessage() . "\n";
    }
} else {
    echo "❌ Laravel is not working properly\n";
}
?>