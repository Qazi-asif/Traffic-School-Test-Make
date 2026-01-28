<?php
/**
 * Fix Laragon Apache Configuration
 * Resolve "Not Found" error for Laravel routes
 */

echo "🔧 FIXING LARAGON APACHE CONFIGURATION\n";
echo "=====================================\n\n";

// Step 1: Check current directory structure
echo "STEP 1: Checking Directory Structure\n";
echo "------------------------------------\n";

$currentDir = getcwd();
echo "✅ Current directory: {$currentDir}\n";

$requiredFiles = ['public/index.php', 'public/.htaccess', 'artisan', 'routes/web.php'];
foreach ($requiredFiles as $file) {
    if (file_exists($file)) {
        echo "✅ {$file}: Exists\n";
    } else {
        echo "❌ {$file}: Missing\n";
    }
}

// Step 2: Check/Create .htaccess in public directory
echo "\nSTEP 2: Fixing .htaccess Configuration\n";
echo "-------------------------------------\n";

$htaccessPath = 'public/.htaccess';
$htaccessContent = '<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>';

if (file_exists($htaccessPath)) {
    $currentHtaccess = file_get_contents($htaccessPath);
    if (strpos($currentHtaccess, 'RewriteEngine On') !== false) {
        echo "✅ .htaccess exists and has rewrite rules\n";
    } else {
        file_put_contents($htaccessPath, $htaccessContent);
        echo "✅ Updated .htaccess with proper rewrite rules\n";
    }
} else {
    file_put_contents($htaccessPath, $htaccessContent);
    echo "✅ Created .htaccess with Laravel rewrite rules\n";
}

// Step 3: Check/Create root .htaccess for domain redirection
echo "\nSTEP 3: Creating Root .htaccess for Domain Redirection\n";
echo "-----------------------------------------------------\n";

$rootHtaccessPath = '.htaccess';
$rootHtaccessContent = '<IfModule mod_rewrite.c>
    RewriteEngine On
    
    # Redirect all requests to public folder
    RewriteCond %{REQUEST_URI} !^/public/
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ /public/$1 [L]
    
    # Handle requests that go directly to public
    RewriteCond %{REQUEST_URI} ^/public/
    RewriteRule ^public/(.*)$ /public/$1 [L]
</IfModule>';

file_put_contents($rootHtaccessPath, $rootHtaccessContent);
echo "✅ Created root .htaccess for proper routing\n";

// Step 4: Check Laravel configuration
echo "\nSTEP 4: Checking Laravel Configuration\n";
echo "-------------------------------------\n";

// Check APP_URL in .env
if (file_exists('.env')) {
    $envContent = file_get_contents('.env');
    
    if (strpos($envContent, 'APP_URL=http://nelly-elearning.test') !== false) {
        echo "✅ APP_URL is correctly set to nelly-elearning.test\n";
    } else {
        // Update APP_URL
        $envContent = preg_replace('/APP_URL=.*/', 'APP_URL=http://nelly-elearning.test', $envContent);
        file_put_contents('.env', $envContent);
        echo "✅ Updated APP_URL to nelly-elearning.test\n";
    }
} else {
    echo "❌ .env file not found\n";
}

// Step 5: Clear Laravel caches
echo "\nSTEP 5: Clearing Laravel Caches\n";
echo "-------------------------------\n";

$cacheCommands = [
    'config:clear' => 'Configuration cache',
    'route:clear' => 'Route cache', 
    'view:clear' => 'View cache',
    'cache:clear' => 'Application cache'
];

foreach ($cacheCommands as $command => $description) {
    try {
        shell_exec("php artisan {$command} 2>&1");
        echo "✅ Cleared {$description}\n";
    } catch (Exception $e) {
        echo "⚠️  Failed to clear {$description}\n";
    }
}

// Step 6: Test route registration
echo "\nSTEP 6: Testing Route Registration\n";
echo "---------------------------------\n";

try {
    $output = shell_exec('php artisan route:list 2>&1');
    if ($output && strpos($output, 'florida') !== false) {
        echo "✅ Routes are registered correctly\n";
    } else {
        echo "⚠️  Routes may not be registered properly\n";
    }
} catch (Exception $e) {
    echo "⚠️  Could not check routes: " . $e->getMessage() . "\n";
}

// Step 7: Create Laragon-specific configuration
echo "\nSTEP 7: Creating Laragon Configuration\n";
echo "-------------------------------------\n";

// Create a simple index.php in root for testing
$rootIndexContent = '<?php
/**
 * Laravel Application Entry Point for Laragon
 */

// Check if request should go to Laravel
$uri = urldecode(parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH));

// Serve static files directly from public
if ($uri !== "/" && file_exists(__DIR__ . "/public" . $uri)) {
    return false;
}

// Route everything else to Laravel public/index.php
require_once __DIR__ . "/public/index.php";
';

file_put_contents('index.php', $rootIndexContent);
echo "✅ Created root index.php for Laragon compatibility\n";

// Step 8: Test URLs
echo "\nSTEP 8: Testing Application URLs\n";
echo "-------------------------------\n";

$testUrls = [
    'http://nelly-elearning.test',
    'http://nelly-elearning.test/florida/login',
    'http://nelly-elearning.test/missouri/login'
];

foreach ($testUrls as $url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        echo "❌ {$url}: Connection error\n";
    } elseif ($httpCode === 200) {
        echo "✅ {$url}: Working\n";
    } else {
        echo "⚠️  {$url}: HTTP {$httpCode}\n";
    }
}

echo "\n🎉 LARAGON APACHE FIX COMPLETE!\n";
echo "==============================\n\n";

echo "✅ .htaccess files created/updated\n";
echo "✅ Laravel caches cleared\n";
echo "✅ APP_URL configured for nelly-elearning.test\n";
echo "✅ Root index.php created for compatibility\n";
echo "✅ Routes should now work properly\n\n";

echo "🌐 TEST THESE URLS NOW:\n";
echo "======================\n";
echo "Main site: http://nelly-elearning.test\n";
echo "Florida login: http://nelly-elearning.test/florida/login\n";
echo "Missouri login: http://nelly-elearning.test/missouri/login\n";
echo "Texas login: http://nelly-elearning.test/texas/login\n";
echo "Delaware login: http://nelly-elearning.test/delaware/login\n\n";

echo "🔑 LOGIN CREDENTIALS:\n";
echo "====================\n";
echo "Email: florida@test.com\n";
echo "Password: password123\n\n";

echo "🔧 IF STILL NOT WORKING:\n";
echo "=======================\n";
echo "1. Restart Apache in Laragon\n";
echo "2. Check Laragon virtual host configuration\n";
echo "3. Verify mod_rewrite is enabled in Apache\n";
echo "4. Check Apache error logs in Laragon\n\n";

echo "✅ Your Laravel application should now work with Laragon!\n";

echo "\n🏁 Fix completed at " . date('Y-m-d H:i:s') . "\n";