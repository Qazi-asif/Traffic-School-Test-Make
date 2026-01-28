<?php
/**
 * Fix Laragon Virtual Host Configuration
 * Resolve Apache "Not Found" error by fixing virtual host setup
 */

echo "🔧 FIXING LARAGON VIRTUAL HOST CONFIGURATION\n";
echo "===========================================\n\n";

// Step 1: Check current directory and structure
echo "STEP 1: Analyzing Current Setup\n";
echo "------------------------------\n";

$currentDir = getcwd();
$projectName = basename($currentDir);
echo "✅ Project directory: {$currentDir}\n";
echo "✅ Project name: {$projectName}\n";

// Step 2: Create proper Laravel entry point
echo "\nSTEP 2: Creating Proper Laravel Entry Point\n";
echo "-------------------------------------------\n";

// Check if we're in the right structure for Laragon
$publicIndexExists = file_exists('public/index.php');
$rootIndexExists = file_exists('index.php');

echo "Public index.php exists: " . ($publicIndexExists ? "✅ Yes" : "❌ No") . "\n";
echo "Root index.php exists: " . ($rootIndexExists ? "✅ Yes" : "❌ No") . "\n";

// Create a proper root index.php that routes to Laravel
$rootIndexContent = '<?php
/**
 * Laragon Entry Point for Laravel
 * Routes all requests to Laravel public directory
 */

$uri = urldecode(parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH));

// If requesting a file that exists in public, serve it directly
if ($uri !== "/" && file_exists(__DIR__ . "/public" . $uri)) {
    return false;
}

// Otherwise, route to Laravel
$_SERVER["SCRIPT_NAME"] = "/index.php";
$_SERVER["SCRIPT_FILENAME"] = __DIR__ . "/public/index.php";

require_once __DIR__ . "/public/index.php";
';

file_put_contents('index.php', $rootIndexContent);
echo "✅ Created/Updated root index.php for Laragon\n";

// Step 3: Fix .htaccess files
echo "\nSTEP 3: Fixing .htaccess Configuration\n";
echo "-------------------------------------\n";

// Root .htaccess
$rootHtaccess = '<IfModule mod_rewrite.c>
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

file_put_contents('.htaccess', $rootHtaccess);
echo "✅ Created/Updated root .htaccess\n";

// Public .htaccess
$publicHtaccess = '<IfModule mod_rewrite.c>
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

if (!is_dir('public')) {
    mkdir('public', 0755, true);
}

file_put_contents('public/.htaccess', $publicHtaccess);
echo "✅ Created/Updated public/.htaccess\n";

// Step 4: Create a simple test page
echo "\nSTEP 4: Creating Test Pages\n";
echo "--------------------------\n";

// Create a simple test.php in root
$testPageContent = '<?php
echo "<h1>✅ Laravel Test Page Working!</h1>";
echo "<p>Server: " . $_SERVER["SERVER_NAME"] . "</p>";
echo "<p>Request URI: " . $_SERVER["REQUEST_URI"] . "</p>";
echo "<p>Document Root: " . $_SERVER["DOCUMENT_ROOT"] . "</p>";
echo "<p>Script Name: " . $_SERVER["SCRIPT_NAME"] . "</p>";
echo "<p>Current Directory: " . __DIR__ . "</p>";
echo "<p>Time: " . date("Y-m-d H:i:s") . "</p>";

echo "<h2>🔗 Test Links:</h2>";
echo "<ul>";
echo "<li><a href=\"/florida/login\">Florida Login</a></li>";
echo "<li><a href=\"/missouri/login\">Missouri Login</a></li>";
echo "<li><a href=\"/texas/login\">Texas Login</a></li>";
echo "<li><a href=\"/delaware/login\">Delaware Login</a></li>";
echo "</ul>";

echo "<h2>📋 Laravel Status:</h2>";
if (file_exists("vendor/autoload.php")) {
    echo "<p>✅ Composer autoload exists</p>";
    
    try {
        require_once "vendor/autoload.php";
        echo "<p>✅ Autoload successful</p>";
        
        if (file_exists("bootstrap/app.php")) {
            echo "<p>✅ Bootstrap file exists</p>";
            
            try {
                $app = require_once "bootstrap/app.php";
                echo "<p>✅ Laravel app loaded successfully</p>";
            } catch (Exception $e) {
                echo "<p>❌ Laravel app load failed: " . $e->getMessage() . "</p>";
            }
        } else {
            echo "<p>❌ Bootstrap file missing</p>";
        }
    } catch (Exception $e) {
        echo "<p>❌ Autoload failed: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p>❌ Composer autoload missing</p>";
}
?>';

file_put_contents('test.php', $testPageContent);
echo "✅ Created test.php for debugging\n";

// Step 5: Update .env for proper URL
echo "\nSTEP 5: Updating Environment Configuration\n";
echo "-----------------------------------------\n";

if (file_exists('.env')) {
    $envContent = file_get_contents('.env');
    
    // Update APP_URL
    if (strpos($envContent, 'APP_URL=') !== false) {
        $envContent = preg_replace('/APP_URL=.*/', 'APP_URL=http://nelly-elearning.test', $envContent);
    } else {
        $envContent .= "\nAPP_URL=http://nelly-elearning.test\n";
    }
    
    file_put_contents('.env', $envContent);
    echo "✅ Updated APP_URL in .env\n";
} else {
    echo "⚠️  .env file not found\n";
}

// Step 6: Clear all Laravel caches
echo "\nSTEP 6: Clearing Laravel Caches\n";
echo "-------------------------------\n";

$commands = [
    'php artisan config:clear',
    'php artisan route:clear',
    'php artisan view:clear',
    'php artisan cache:clear'
];

foreach ($commands as $command) {
    try {
        $output = shell_exec($command . ' 2>&1');
        echo "✅ Executed: {$command}\n";
    } catch (Exception $e) {
        echo "⚠️  Failed: {$command} - " . $e->getMessage() . "\n";
    }
}

// Step 7: Create Laragon-specific configuration
echo "\nSTEP 7: Creating Laragon Configuration Guide\n";
echo "-------------------------------------------\n";

$configGuide = "🔧 LARAGON CONFIGURATION GUIDE
==============================

CURRENT STATUS:
- Project: {$projectName}
- Directory: {$currentDir}
- Domain: nelly-elearning.test

LARAGON SETUP STEPS:
1. Open Laragon
2. Right-click Laragon tray icon
3. Go to Apache > sites-enabled
4. Check if nelly-elearning.test.conf exists
5. If not, create it with this content:

<VirtualHost *:80>
    DocumentRoot \"{$currentDir}\"
    ServerName nelly-elearning.test
    ServerAlias *.nelly-elearning.test
    
    <Directory \"{$currentDir}\">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>

ALTERNATIVE QUICK FIX:
1. In Laragon, go to Menu > Tools > Quick add
2. Add: nelly-elearning.test -> {$currentDir}
3. Restart Apache

HOSTS FILE CHECK:
Make sure this line exists in C:\\Windows\\System32\\drivers\\etc\\hosts:
127.0.0.1    nelly-elearning.test

TEST URLS AFTER FIX:
- http://nelly-elearning.test/test.php (should show test page)
- http://nelly-elearning.test/florida/login (Laravel route)
- http://nelly-elearning.test (main Laravel app)
";

file_put_contents('LARAGON_CONFIG_GUIDE.txt', $configGuide);
echo "✅ Created LARAGON_CONFIG_GUIDE.txt\n";

// Step 8: Final diagnostics
echo "\nSTEP 8: Final Diagnostics\n";
echo "------------------------\n";

echo "✅ Files created/updated:\n";
echo "   - index.php (root entry point)\n";
echo "   - .htaccess (root rewrite rules)\n";
echo "   - public/.htaccess (Laravel rewrite rules)\n";
echo "   - test.php (debugging page)\n";
echo "   - LARAGON_CONFIG_GUIDE.txt (setup guide)\n\n";

echo "🎯 IMMEDIATE NEXT STEPS:\n";
echo "=======================\n";
echo "1. Visit: http://nelly-elearning.test/test.php\n";
echo "   (This should work immediately)\n\n";
echo "2. If test.php works but Laravel routes don't:\n";
echo "   - Follow LARAGON_CONFIG_GUIDE.txt\n";
echo "   - Restart Apache in Laragon\n\n";
echo "3. If test.php doesn't work:\n";
echo "   - Check Laragon virtual host configuration\n";
echo "   - Verify hosts file entry\n";
echo "   - Restart Laragon completely\n\n";

echo "🔑 ONCE WORKING, TEST THESE:\n";
echo "===========================\n";
echo "- http://nelly-elearning.test/florida/login\n";
echo "- Login: florida@test.com / password123\n\n";

echo "✅ Laragon virtual host fix completed!\n";

echo "\n🏁 Fix completed at " . date('Y-m-d H:i:s') . "\n";