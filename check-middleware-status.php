<?php

require_once 'vendor/autoload.php';

echo "🔍 CHECKING MIDDLEWARE STATUS\n";
echo "=============================\n\n";

// Check if middleware files exist
$testMiddleware = 'app/Http/Middleware/TestModuleMiddleware.php';
$mainMiddleware = 'app/Http/Middleware/ModuleAccessMiddleware.php';

echo "📁 CHECKING FILES:\n";
echo "- TestModuleMiddleware: " . (file_exists($testMiddleware) ? "✅ EXISTS" : "❌ MISSING") . "\n";
echo "- ModuleAccessMiddleware: " . (file_exists($mainMiddleware) ? "✅ EXISTS" : "❌ MISSING") . "\n\n";

// Check Kernel.php registration
$kernelFile = 'app/Http/Kernel.php';
if (file_exists($kernelFile)) {
    $kernelContent = file_get_contents($kernelFile);
    echo "🔧 CHECKING KERNEL REGISTRATION:\n";
    
    if (strpos($kernelContent, 'TestModuleMiddleware') !== false) {
        echo "- TestModuleMiddleware: ✅ REGISTERED\n";
    } else {
        echo "- TestModuleMiddleware: ❌ NOT REGISTERED\n";
    }
    
    if (strpos($kernelContent, 'ModuleAccessMiddleware') !== false) {
        echo "- ModuleAccessMiddleware: ✅ REGISTERED\n";
    } else {
        echo "- ModuleAccessMiddleware: ❌ NOT REGISTERED\n";
    }
} else {
    echo "❌ Kernel.php not found\n";
}

echo "\n📝 CHECKING LARAVEL LOGS:\n";
$logFile = 'storage/logs/laravel.log';
if (file_exists($logFile)) {
    $logContent = file_get_contents($logFile);
    $lines = explode("\n", $logContent);
    $recentLines = array_slice($lines, -50); // Last 50 lines
    
    $middlewareFound = false;
    foreach ($recentLines as $line) {
        if (strpos($line, 'TEST MIDDLEWARE RUNNING') !== false) {
            echo "✅ Found middleware log: " . trim($line) . "\n";
            $middlewareFound = true;
        }
    }
    
    if (!$middlewareFound) {
        echo "❌ No middleware logs found in recent entries\n";
        echo "💡 This means middleware is NOT running\n";
    }
} else {
    echo "❌ Laravel log file not found\n";
}

echo "\n🔧 TROUBLESHOOTING STEPS:\n";
echo "1. Check if web server needs restart\n";
echo "2. Clear all caches\n";
echo "3. Check for PHP syntax errors\n";
echo "4. Verify middleware is in correct middleware group\n";

echo "\n🚨 EMERGENCY FIX:\n";
echo "If middleware isn't working, we can add blocking directly to routes or controllers.\n";

echo "\n✅ Check completed!\n";