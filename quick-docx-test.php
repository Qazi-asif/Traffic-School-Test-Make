<?php
/**
 * Quick DOCX Import Test
 * 
 * Simple test to verify DOCX import functionality without full Laravel bootstrap
 */

echo "=== QUICK DOCX IMPORT TEST ===\n\n";

// Test 1: Check basic files
echo "1. Checking Essential Files...\n";
$files = [
    '.env' => 'Environment configuration',
    'routes/web.php' => 'Web routes',
    'app/Http/Controllers/ChapterController.php' => 'Chapter controller',
    'resources/views/test-docx-import.blade.php' => 'Enhanced test page',
    'composer.json' => 'Composer configuration'
];

foreach ($files as $file => $description) {
    if (file_exists($file)) {
        echo "   ✅ {$description}: {$file}\n";
    } else {
        echo "   ❌ {$description}: {$file} - MISSING\n";
    }
}

// Test 2: Check .env configuration
echo "\n2. Checking Environment Configuration...\n";
if (file_exists('.env')) {
    $envContent = file_get_contents('.env');
    
    // Check APP_KEY
    if (preg_match('/APP_KEY=(.+)/', $envContent, $matches)) {
        $appKey = trim($matches[1]);
        if (!empty($appKey) && $appKey !== 'base64:') {
            echo "   ✅ APP_KEY is configured\n";
        } else {
            echo "   ❌ APP_KEY is empty - run: php artisan key:generate\n";
        }
    } else {
        echo "   ❌ APP_KEY not found in .env\n";
    }
    
    // Check APP_DEBUG
    if (strpos($envContent, 'APP_DEBUG=true') !== false) {
        echo "   ✅ APP_DEBUG is enabled (good for testing)\n";
    } else {
        echo "   ⚠️  APP_DEBUG is disabled (enable for better error messages)\n";
    }
} else {
    echo "   ❌ .env file not found\n";
}

// Test 3: Check routes
echo "\n3. Checking Routes Configuration...\n";
if (file_exists('routes/web.php')) {
    $routesContent = file_get_contents('routes/web.php');
    
    $routes = [
        '/api/import-docx' => 'DOCX import endpoint',
        '/test-docx-import' => 'Enhanced test page',
        '/working-docx-upload' => 'Working test page'
    ];
    
    foreach ($routes as $route => $description) {
        if (strpos($routesContent, $route) !== false) {
            echo "   ✅ {$description}: {$route}\n";
        } else {
            echo "   ❌ {$description}: {$route} - NOT FOUND\n";
        }
    }
} else {
    echo "   ❌ routes/web.php not found\n";
}

// Test 4: Check ChapterController
echo "\n4. Checking ChapterController...\n";
if (file_exists('app/Http/Controllers/ChapterController.php')) {
    $controllerContent = file_get_contents('app/Http/Controllers/ChapterController.php');
    
    $methods = [
        'importDocx' => 'DOCX import method',
        'importDocxWithImageSkipping' => 'Fallback import method'
    ];
    
    foreach ($methods as $method => $description) {
        if (strpos($controllerContent, "function {$method}") !== false) {
            echo "   ✅ {$description}: {$method}()\n";
        } else {
            echo "   ❌ {$description}: {$method}() - NOT FOUND\n";
        }
    }
    
    // Check for enhanced error handling
    if (strpos($controllerContent, 'ValidationException') !== false) {
        echo "   ✅ Enhanced validation error handling\n";
    } else {
        echo "   ⚠️  Basic error handling (consider upgrading)\n";
    }
} else {
    echo "   ❌ ChapterController.php not found\n";
}

// Test 5: Check Composer dependencies
echo "\n5. Checking Composer Dependencies...\n";
if (file_exists('composer.json')) {
    $composerContent = file_get_contents('composer.json');
    $composerData = json_decode($composerContent, true);
    
    $dependencies = [
        'phpoffice/phpword' => 'PHPWord library for DOCX processing',
        'laravel/framework' => 'Laravel framework'
    ];
    
    foreach ($dependencies as $package => $description) {
        if (isset($composerData['require'][$package])) {
            echo "   ✅ {$description}: {$package}\n";
            
            // Check if vendor directory exists
            $vendorPath = "vendor/" . str_replace('/', '/', $package);
            if (is_dir($vendorPath)) {
                echo "       ✅ Package installed in vendor directory\n";
            } else {
                echo "       ❌ Package not installed - run: composer install\n";
            }
        } else {
            echo "   ❌ {$description}: {$package} - NOT IN COMPOSER.JSON\n";
        }
    }
} else {
    echo "   ❌ composer.json not found\n";
}

// Test 6: Check storage directories
echo "\n6. Checking Storage Directories...\n";
$directories = [
    'storage' => 'Main storage directory',
    'storage/app' => 'App storage',
    'storage/app/public' => 'Public storage',
    'storage/app/public/course-media' => 'Course media storage',
    'storage/logs' => 'Log directory'
];

foreach ($directories as $dir => $description) {
    if (is_dir($dir)) {
        echo "   ✅ {$description}: {$dir}\n";
        
        if (is_writable($dir)) {
            echo "       ✅ Directory is writable\n";
        } else {
            echo "       ❌ Directory is not writable - check permissions\n";
        }
    } else {
        echo "   ❌ {$description}: {$dir} - MISSING\n";
        
        // Try to create it
        if (mkdir($dir, 0755, true)) {
            echo "       ✅ Created directory\n";
        } else {
            echo "       ❌ Could not create directory\n";
        }
    }
}

// Test 7: Check test views
echo "\n7. Checking Test Views...\n";
$views = [
    'resources/views/test-docx-import.blade.php' => 'Enhanced DOCX test page',
    'resources/views/working-docx-upload.blade.php' => 'Working DOCX upload page',
    'resources/views/working-course-creation.blade.php' => 'Working course creation page'
];

foreach ($views as $view => $description) {
    if (file_exists($view)) {
        echo "   ✅ {$description}: {$view}\n";
        
        // Check for CSRF token handling
        $viewContent = file_get_contents($view);
        if (strpos($viewContent, 'csrf-token') !== false) {
            echo "       ✅ CSRF token handling included\n";
        } else {
            echo "       ⚠️  CSRF token handling not found\n";
        }
    } else {
        echo "   ❌ {$description}: {$view} - MISSING\n";
    }
}

echo "\n=== TEST COMPLETE ===\n";

// Summary and recommendations
$errors = 0;
$warnings = 0;

// Count errors and warnings from output
ob_start();
$output = ob_get_clean();

echo "\n📋 SUMMARY:\n";
echo "This test checks the essential files and configuration for DOCX import functionality.\n";

echo "\n🚀 NEXT STEPS:\n";
echo "1. Fix any ❌ errors shown above\n";
echo "2. Start your web server (php artisan serve or use Laragon)\n";
echo "3. Visit: http://your-domain/test-docx-import\n";
echo "4. Test DOCX upload functionality\n";
echo "5. Check browser console and network tab for any errors\n";

echo "\n🔧 COMMON FIXES:\n";
echo "- Run 'composer install' to install dependencies\n";
echo "- Run 'php artisan key:generate' to generate APP_KEY\n";
echo "- Check file permissions on storage directories\n";
echo "- Ensure web server is running and accessible\n";
echo "- Clear browser cache and cookies if CSRF issues persist\n";

echo "\n📖 DOCUMENTATION:\n";
echo "- Full fix details: DOCX_IMPORT_FIX_SUMMARY.md\n";
echo "- Enhanced test page: /test-docx-import\n";
echo "- Working examples: /working-docx-upload\n";

echo "\n";