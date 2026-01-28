<?php
/**
 * Server Startup Helper
 * Check server status and provide startup instructions
 */

echo "🚀 Laravel Server Startup Helper\n";
echo "================================\n\n";

// Check if we're in a Laravel project
if (!file_exists('artisan')) {
    echo "❌ Error: Not in a Laravel project directory\n";
    echo "Please navigate to your Laravel project folder first.\n";
    exit(1);
}

echo "✅ Laravel project detected\n";

// Check current directory
$currentDir = getcwd();
echo "📁 Current directory: {$currentDir}\n\n";

// Check if server is already running
echo "🔍 Checking if server is already running...\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://127.0.0.1:8000');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 2);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($httpCode === 200) {
    echo "✅ Server is already running on http://127.0.0.1:8000\n";
    echo "✅ You can access your application at: http://127.0.0.1:8000\n\n";
    
    echo "🔑 LOGIN URLS (using localhost):\n";
    echo "Florida: http://127.0.0.1:8000/florida/login\n";
    echo "Missouri: http://127.0.0.1:8000/missouri/login\n";
    echo "Texas: http://127.0.0.1:8000/texas/login\n";
    echo "Delaware: http://127.0.0.1:8000/delaware/login\n\n";
    
    echo "👤 TEST CREDENTIALS:\n";
    echo "florida@test.com / password123\n";
    echo "missouri@test.com / password123\n";
    echo "texas@test.com / password123\n";
    echo "delaware@test.com / password123\n";
    echo "admin@test.com / admin123\n";
    
} else {
    echo "❌ Server is not running\n\n";
    
    echo "🚀 TO START THE SERVER:\n";
    echo "======================\n";
    echo "Run this command in your terminal:\n\n";
    echo "   php artisan serve\n\n";
    echo "Or if you want to specify host and port:\n\n";
    echo "   php artisan serve --host=127.0.0.1 --port=8000\n\n";
    
    echo "📋 ALTERNATIVE METHODS:\n";
    echo "======================\n";
    echo "1. Using Laragon (if installed):\n";
    echo "   - Start Laragon\n";
    echo "   - Make sure Apache/Nginx is running\n";
    echo "   - Access via: http://nelly-elearning.test\n\n";
    
    echo "2. Using XAMPP (if installed):\n";
    echo "   - Start XAMPP Control Panel\n";
    echo "   - Start Apache\n";
    echo "   - Configure virtual host for nelly-elearning.test\n\n";
    
    echo "3. Using built-in PHP server (simplest):\n";
    echo "   - Run: php artisan serve\n";
    echo "   - Access via: http://127.0.0.1:8000\n\n";
}

echo "⚠️  IMPORTANT NOTES:\n";
echo "===================\n";
echo "- If using 'php artisan serve', use http://127.0.0.1:8000 URLs\n";
echo "- If using Laragon/XAMPP, use http://nelly-elearning.test URLs\n";
echo "- Make sure your database is running (MySQL/MariaDB)\n";
echo "- Check .env file for correct database settings\n\n";

echo "🔧 TROUBLESHOOTING:\n";
echo "==================\n";
echo "If you get 'php command not found':\n";
echo "- Make sure PHP is installed and in your PATH\n";
echo "- Try using full path to PHP executable\n\n";

echo "If you get database connection errors:\n";
echo "- Start your MySQL/MariaDB service\n";
echo "- Check database credentials in .env file\n";
echo "- Run: php artisan migrate (if needed)\n\n";

echo "🏁 Server startup helper completed\n";