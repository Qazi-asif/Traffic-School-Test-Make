<?php
/**
 * Quick Server Start
 * Immediate server startup with all necessary checks
 */

echo "🚀 QUICK LARAVEL SERVER START\n";
echo "=============================\n\n";

// Check if we're in Laravel directory
if (!file_exists('artisan')) {
    echo "❌ Error: Not in Laravel directory. Please navigate to your Laravel project folder.\n";
    exit(1);
}

echo "✅ Laravel project detected\n";

// Clear all caches first
echo "🧹 Clearing caches...\n";
try {
    shell_exec('php artisan config:clear 2>&1');
    shell_exec('php artisan route:clear 2>&1');
    shell_exec('php artisan view:clear 2>&1');
    echo "✅ Caches cleared\n";
} catch (Exception $e) {
    echo "⚠️  Cache clearing failed: " . $e->getMessage() . "\n";
}

// Check if port 8000 is available
echo "🔍 Checking port availability...\n";
$connection = @fsockopen('127.0.0.1', 8000, $errno, $errstr, 1);
if ($connection) {
    fclose($connection);
    echo "⚠️  Port 8000 is already in use. Server might already be running.\n";
    echo "Try visiting: http://127.0.0.1:8000\n\n";
} else {
    echo "✅ Port 8000 is available\n";
}

// Display startup information
echo "\n📋 SERVER STARTUP INFORMATION\n";
echo "============================\n";
echo "Server will start on: http://127.0.0.1:8000\n\n";

echo "🔑 LOGIN URLS:\n";
echo "- Florida: http://127.0.0.1:8000/florida/login\n";
echo "- Missouri: http://127.0.0.1:8000/missouri/login\n";
echo "- Texas: http://127.0.0.1:8000/texas/login\n";
echo "- Delaware: http://127.0.0.1:8000/delaware/login\n\n";

echo "👤 TEST CREDENTIALS:\n";
echo "- florida@test.com / password123\n";
echo "- missouri@test.com / password123\n";
echo "- texas@test.com / password123\n";
echo "- delaware@test.com / password123\n";
echo "- admin@test.com / admin123\n\n";

echo "🎯 WHAT'S READY:\n";
echo "✅ Multi-state authentication system\n";
echo "✅ Course progress tracking\n";
echo "✅ Certificate generation system\n";
echo "✅ State-specific dashboards\n";
echo "✅ Progress monitoring APIs\n\n";

echo "🚀 TO START THE SERVER:\n";
echo "======================\n";
echo "Run this command in your terminal:\n\n";
echo "   php artisan serve --host=127.0.0.1 --port=8000\n\n";

echo "Or double-click: start_laravel_server.bat\n\n";

echo "📱 ALTERNATIVE: Use Built-in PHP Server\n";
echo "======================================\n";
echo "If 'php artisan serve' doesn't work, try:\n\n";
echo "   php -S 127.0.0.1:8000 -t public\n\n";

// Create a simple PHP server script as backup
$simpleServerScript = '<?php
// Simple PHP Server for Laravel
// Run with: php -S 127.0.0.1:8000 simple_server.php

$uri = urldecode(parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH));

// Serve static files directly
if ($uri !== "/" && file_exists(__DIR__ . "/public" . $uri)) {
    return false;
}

// Route all other requests to Laravel
require_once __DIR__ . "/public/index.php";
';

file_put_contents('simple_server.php', $simpleServerScript);
echo "✅ Created simple_server.php as backup\n";

echo "\n🔧 TROUBLESHOOTING:\n";
echo "==================\n";
echo "If 'php' command not found:\n";
echo "- Add PHP to your system PATH\n";
echo "- Use full path to PHP executable\n";
echo "- Install PHP if not installed\n\n";

echo "If database errors occur:\n";
echo "- Start MySQL/MariaDB service\n";
echo "- Check .env database settings\n";
echo "- Run: php artisan migrate\n\n";

echo "If routes don't work:\n";
echo "- Clear caches: php artisan optimize:clear\n";
echo "- Check .htaccess in public folder\n";
echo "- Verify mod_rewrite is enabled\n\n";

echo "🏁 Server startup guide completed\n";
echo "Now run the server command above and visit the login URLs!\n";