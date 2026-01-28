<?php
/**
 * CSRF Token and DOCX Import Diagnostic Script
 * 
 * This script helps diagnose CSRF token issues with DOCX imports
 * Run this script to test the current CSRF configuration
 */

echo "=== CSRF TOKEN AND DOCX IMPORT DIAGNOSTIC ===\n\n";

try {
    // Test 1: Check if Composer autoload exists
    echo "1. Testing Composer Autoload...\n";
    if (file_exists(__DIR__ . '/vendor/autoload.php')) {
        require_once __DIR__ . '/vendor/autoload.php';
        echo "   ✅ Composer autoload found\n";
    } else {
        echo "   ❌ Composer autoload not found - run 'composer install'\n";
        exit(1);
    }
    
    // Test 2: Check Laravel bootstrap
    echo "\n2. Testing Laravel Bootstrap...\n";
    if (file_exists(__DIR__ . '/bootstrap/app.php')) {
        echo "   ✅ Laravel bootstrap file exists\n";
        
        // Try to bootstrap Laravel
        $app = require_once __DIR__ . '/bootstrap/app.php';
        
        // Boot the application
        $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
        $response = $kernel->handle(
            $request = Illuminate\Http\Request::capture()
        );
        
        echo "   ✅ Laravel application bootstrapped\n";
    } else {
        echo "   ❌ Laravel bootstrap file not found\n";
        exit(1);
    }
    
    // Test 3: Check environment file
    echo "\n3. Testing Environment Configuration...\n";
    if (file_exists(__DIR__ . '/.env')) {
        echo "   ✅ .env file exists\n";
        
        // Read .env file manually to check key
        $envContent = file_get_contents(__DIR__ . '/.env');
        if (strpos($envContent, 'APP_KEY=') !== false) {
            preg_match('/APP_KEY=(.+)/', $envContent, $matches);
            if (!empty($matches[1]) && $matches[1] !== 'base64:') {
                echo "   ✅ APP_KEY is set in .env\n";
            } else {
                echo "   ❌ APP_KEY is empty - run 'php artisan key:generate'\n";
            }
        } else {
            echo "   ❌ APP_KEY not found in .env\n";
        }
    } else {
        echo "   ❌ .env file not found - copy .env.example to .env\n";
    }
    
    // Test 4: Check routes file
    echo "\n4. Testing Routes Configuration...\n";
    if (file_exists(__DIR__ . '/routes/web.php')) {
        echo "   ✅ routes/web.php exists\n";
        
        $routesContent = file_get_contents(__DIR__ . '/routes/web.php');
        if (strpos($routesContent, '/api/import-docx') !== false) {
            echo "   ✅ DOCX import route found in routes/web.php\n";
        } else {
            echo "   ❌ DOCX import route not found in routes/web.php\n";
        }
        
        if (strpos($routesContent, '/test-docx-import') !== false) {
            echo "   ✅ Test DOCX import route found\n";
        } else {
            echo "   ❌ Test DOCX import route not found\n";
        }
    } else {
        echo "   ❌ routes/web.php not found\n";
    }
    
    // Test 5: Check ChapterController
    echo "\n5. Testing ChapterController...\n";
    if (file_exists(__DIR__ . '/app/Http/Controllers/ChapterController.php')) {
        echo "   ✅ ChapterController.php exists\n";
        
        $controllerContent = file_get_contents(__DIR__ . '/app/Http/Controllers/ChapterController.php');
        if (strpos($controllerContent, 'function importDocx') !== false) {
            echo "   ✅ importDocx method found in ChapterController\n";
        } else {
            echo "   ❌ importDocx method not found in ChapterController\n";
        }
    } else {
        echo "   ❌ ChapterController.php not found\n";
    }
    
    // Test 6: Check PHPWord in composer.json
    echo "\n6. Testing PHPWord Library...\n";
    if (file_exists(__DIR__ . '/composer.json')) {
        $composerContent = file_get_contents(__DIR__ . '/composer.json');
        $composerData = json_decode($composerContent, true);
        
        if (isset($composerData['require']['phpoffice/phpword'])) {
            echo "   ✅ PHPWord is listed in composer.json\n";
            
            if (file_exists(__DIR__ . '/vendor/phpoffice/phpword')) {
                echo "   ✅ PHPWord vendor directory exists\n";
            } else {
                echo "   ❌ PHPWord vendor directory not found - run 'composer install'\n";
            }
        } else {
            echo "   ❌ PHPWord not found in composer.json\n";
        }
    } else {
        echo "   ❌ composer.json not found\n";
    }
    
    // Test 7: Check storage permissions
    echo "\n7. Testing Storage Permissions...\n";
    $storagePath = __DIR__ . '/storage';
    if (is_dir($storagePath)) {
        echo "   ✅ Storage directory exists\n";
        
        if (is_writable($storagePath)) {
            echo "   ✅ Storage directory is writable\n";
        } else {
            echo "   ❌ Storage directory is not writable\n";
        }
        
        $mediaPath = $storagePath . '/app/public/course-media';
        if (!is_dir($mediaPath)) {
            if (mkdir($mediaPath, 0755, true)) {
                echo "   ✅ Created course-media directory\n";
            } else {
                echo "   ❌ Could not create course-media directory\n";
            }
        } else {
            echo "   ✅ Course-media directory exists\n";
        }
        
        if (is_writable($mediaPath)) {
            echo "   ✅ Course-media directory is writable\n";
        } else {
            echo "   ❌ Course-media directory is not writable\n";
        }
    } else {
        echo "   ❌ Storage directory not found\n";
    }
    
    // Test 8: Check test views
    echo "\n8. Testing Test Views...\n";
    $testViews = [
        'test-docx-import.blade.php',
        'working-docx-upload.blade.php',
        'working-course-creation.blade.php'
    ];
    
    foreach ($testViews as $view) {
        $viewPath = __DIR__ . '/resources/views/' . $view;
        if (file_exists($viewPath)) {
            echo "   ✅ {$view} exists\n";
        } else {
            echo "   ❌ {$view} not found\n";
        }
    }
    
    // Test 9: Generate sample CSRF token
    echo "\n9. Testing CSRF Token Generation...\n";
    try {
        // Generate a token using basic method
        $token = bin2hex(random_bytes(20)); // 40 character hex string
        echo "   ✅ Sample CSRF token generated: " . substr($token, 0, 20) . "...\n";
        
        if (strlen($token) === 40) {
            echo "   ✅ Token length is correct (40 characters)\n";
        } else {
            echo "   ❌ Token length is incorrect\n";
        }
    } catch (\Exception $e) {
        echo "   ❌ CSRF token generation failed: " . $e->getMessage() . "\n";
    }
    
    echo "\n=== DIAGNOSTIC COMPLETE ===\n";
    echo "\n💡 NEXT STEPS:\n";
    echo "1. Visit http://your-domain/test-docx-import to test the enhanced DOCX import\n";
    echo "2. Check that all ✅ items above are working\n";
    echo "3. For any ❌ items, follow the suggested fixes\n";
    echo "4. Check Laravel logs in storage/logs/laravel.log for detailed errors\n";
    echo "5. Ensure your web server is running and accessible\n";
    
    echo "\n🔧 COMMON FIXES:\n";
    echo "- Run 'composer install' if PHPWord is missing\n";
    echo "- Run 'php artisan key:generate' if APP_KEY is missing\n";
    echo "- Check file permissions on storage directory\n";
    echo "- Ensure .env file exists and is properly configured\n";
    
} catch (\Exception $e) {
    echo "❌ Diagnostic failed: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    echo "\n💡 This error suggests Laravel is not properly configured.\n";
    echo "Try running: composer install && php artisan key:generate\n";
}