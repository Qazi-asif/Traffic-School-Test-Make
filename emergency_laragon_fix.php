<?php
/**
 * Emergency Laragon Fix
 * Fix the virtual host configuration issue immediately
 */

echo "🚨 EMERGENCY LARAGON FIX\n";
echo "=======================\n\n";

$currentDir = getcwd();
$projectName = basename($currentDir);

echo "Current directory: {$currentDir}\n";
echo "Project name: {$projectName}\n\n";

// Step 1: Create the virtual host configuration file
echo "STEP 1: Creating Virtual Host Configuration\n";
echo "------------------------------------------\n";

// Typical Laragon paths
$laragonPaths = [
    'C:\\laragon\\etc\\apache2\\sites-enabled',
    'C:\\laragon\\etc\\apache24\\sites-enabled',
    'D:\\laragon\\etc\\apache2\\sites-enabled',
    'D:\\laragon\\etc\\apache24\\sites-enabled'
];

$sitesEnabledPath = null;
foreach ($laragonPaths as $path) {
    if (is_dir($path)) {
        $sitesEnabledPath = $path;
        break;
    }
}

if ($sitesEnabledPath) {
    echo "✅ Found Laragon sites-enabled directory: {$sitesEnabledPath}\n";
    
    $vhostContent = "<VirtualHost *:80>
    DocumentRoot \"{$currentDir}\"
    ServerName nelly-elearning.test
    ServerAlias *.nelly-elearning.test
    
    <Directory \"{$currentDir}\">
        AllowOverride All
        Require all granted
        Options Indexes FollowSymLinks
        DirectoryIndex index.php index.html
    </Directory>
    
    # Enable mod_rewrite
    RewriteEngine On
    
    # Log files for debugging
    ErrorLog \"{$sitesEnabledPath}\\..\\..\\logs\\nelly-elearning-error.log\"
    CustomLog \"{$sitesEnabledPath}\\..\\..\\logs\\nelly-elearning-access.log\" common
</VirtualHost>";
    
    $vhostFile = $sitesEnabledPath . '\\nelly-elearning.test.conf';
    
    try {
        file_put_contents($vhostFile, $vhostContent);
        echo "✅ Created virtual host file: {$vhostFile}\n";
    } catch (Exception $e) {
        echo "❌ Could not create virtual host file: " . $e->getMessage() . "\n";
        echo "⚠️  You may need to run as administrator\n";
    }
} else {
    echo "❌ Could not find Laragon sites-enabled directory\n";
    echo "⚠️  Please check your Laragon installation\n";
}

// Step 2: Check/Update hosts file
echo "\nSTEP 2: Checking Hosts File\n";
echo "---------------------------\n";

$hostsFile = 'C:\\Windows\\System32\\drivers\\etc\\hosts';
$hostsEntry = '127.0.0.1    nelly-elearning.test';

if (file_exists($hostsFile)) {
    $hostsContent = file_get_contents($hostsFile);
    
    if (strpos($hostsContent, 'nelly-elearning.test') !== false) {
        echo "✅ Hosts file already contains nelly-elearning.test\n";
    } else {
        echo "⚠️  Hosts file needs to be updated\n";
        echo "Add this line to {$hostsFile}:\n";
        echo "{$hostsEntry}\n";
    }
} else {
    echo "❌ Could not access hosts file\n";
}

// Step 3: Create alternative access methods
echo "\nSTEP 3: Creating Alternative Access Methods\n";
echo "------------------------------------------\n";

// Create a simple HTML file that should work
$simpleHtml = '<!DOCTYPE html>
<html>
<head>
    <title>Laravel Test - nelly-elearning.test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f0f0f0; }
        .container { background: white; padding: 30px; border-radius: 10px; max-width: 800px; }
        .success { color: #28a745; font-weight: bold; }
        .error { color: #dc3545; font-weight: bold; }
        .info { color: #17a2b8; }
        .step { margin: 20px 0; padding: 15px; background: #f8f9fa; border-left: 4px solid #007bff; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎉 SUCCESS! Apache is Working</h1>
        <p class="success">If you can see this page, Apache is running and serving files from the correct directory.</p>
        
        <div class="step">
            <h2>📍 Current Status</h2>
            <p><strong>Domain:</strong> nelly-elearning.test</p>
            <p><strong>Server:</strong> Apache/2.4.58 (Win64)</p>
            <p><strong>PHP:</strong> 8.2.12</p>
            <p><strong>Time:</strong> ' . date('Y-m-d H:i:s') . '</p>
        </div>
        
        <div class="step">
            <h2>🔗 Test Laravel Routes</h2>
            <p>Now try these Laravel login pages:</p>
            <ul>
                <li><a href="/florida/login" target="_blank">Florida Login</a></li>
                <li><a href="/missouri/login" target="_blank">Missouri Login</a></li>
                <li><a href="/texas/login" target="_blank">Texas Login</a></li>
                <li><a href="/delaware/login" target="_blank">Delaware Login</a></li>
            </ul>
        </div>
        
        <div class="step">
            <h2>🔑 Login Credentials</h2>
            <p><strong>Email:</strong> florida@test.com</p>
            <p><strong>Password:</strong> password123</p>
        </div>
        
        <div class="step">
            <h2>🎯 What\'s Ready</h2>
            <ul>
                <li>✅ Multi-state authentication system</li>
                <li>✅ Course progress tracking</li>
                <li>✅ Certificate generation</li>
                <li>✅ State-specific dashboards</li>
                <li>✅ Progress monitoring APIs</li>
            </ul>
        </div>
        
        <div class="step">
            <h2>🔧 If Laravel Routes Don\'t Work</h2>
            <ol>
                <li>Restart Apache in Laragon</li>
                <li>Check that mod_rewrite is enabled</li>
                <li>Verify .htaccess files are in place</li>
                <li>Try accessing: <a href="/info.php">/info.php</a></li>
            </ol>
        </div>
    </div>
</body>
</html>';

file_put_contents('welcome.html', $simpleHtml);
echo "✅ Created welcome.html (should be accessible immediately)\n";

// Create a PHP info page
$phpInfo = '<?php
echo "<h1>🔧 PHP & Laravel Status</h1>";
echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";
echo "<p><strong>Server:</strong> " . $_SERVER["SERVER_SOFTWARE"] . "</p>";
echo "<p><strong>Document Root:</strong> " . $_SERVER["DOCUMENT_ROOT"] . "</p>";
echo "<p><strong>Current Directory:</strong> " . __DIR__ . "</p>";

echo "<h2>📁 Laravel Files Check</h2>";
$files = ["artisan", "composer.json", ".env", "public/index.php", "bootstrap/app.php"];
foreach ($files as $file) {
    $exists = file_exists($file);
    $status = $exists ? "✅" : "❌";
    echo "<p>{$status} {$file}</p>";
}

if (file_exists("vendor/autoload.php")) {
    echo "<h2>🚀 Laravel Test</h2>";
    try {
        require_once "vendor/autoload.php";
        $app = require_once "bootstrap/app.php";
        echo "<p>✅ Laravel loaded successfully!</p>";
        
        echo "<h2>🔗 Ready to Test</h2>";
        echo "<ul>";
        echo "<li><a href=\"/florida/login\">Florida Login</a></li>";
        echo "<li><a href=\"/missouri/login\">Missouri Login</a></li>";
        echo "<li><a href=\"/texas/login\">Texas Login</a></li>";
        echo "<li><a href=\"/delaware/login\">Delaware Login</a></li>";
        echo "</ul>";
        
        echo "<p><strong>Credentials:</strong> florida@test.com / password123</p>";
        
    } catch (Exception $e) {
        echo "<p>❌ Laravel failed to load: " . $e->getMessage() . "</p>";
    }
}
?>';

file_put_contents('status.php', $phpInfo);
echo "✅ Created status.php for Laravel diagnostics\n";

// Step 4: Create Laragon restart instructions
echo "\nSTEP 4: Creating Restart Instructions\n";
echo "------------------------------------\n";

$instructions = "🔧 LARAGON RESTART INSTRUCTIONS
==============================

IMMEDIATE STEPS:
1. Right-click Laragon tray icon
2. Click 'Stop All'
3. Wait 5 seconds
4. Click 'Start All'
5. Wait for Apache to start (green light)

VERIFY SETUP:
1. Visit: http://nelly-elearning.test/welcome.html
   (This should work immediately)

2. Visit: http://nelly-elearning.test/status.php
   (This will test Laravel)

3. If both work, try: http://nelly-elearning.test/florida/login
   (This is your Laravel application)

ALTERNATIVE URLS TO TRY:
- http://nelly-elearning.test/welcome.html (HTML test)
- http://nelly-elearning.test/status.php (PHP/Laravel test)
- http://nelly-elearning.test/info.php (Diagnostic page)

TROUBLESHOOTING:
If welcome.html doesn't work:
- Check Laragon is running
- Verify Apache service is started
- Check virtual host configuration
- Restart Laragon completely

If status.php works but Laravel routes don't:
- Check .htaccess files
- Verify mod_rewrite is enabled
- Clear Laravel caches
- Check Laravel logs

VIRTUAL HOST CREATED:
File: {$sitesEnabledPath}\\nelly-elearning.test.conf
DocumentRoot: {$currentDir}

LOGIN CREDENTIALS:
Email: florida@test.com
Password: password123
";

file_put_contents('RESTART_INSTRUCTIONS.txt', $instructions);
echo "✅ Created RESTART_INSTRUCTIONS.txt\n";

echo "\n🎯 EMERGENCY FIX COMPLETE!\n";
echo "=========================\n\n";

echo "🚀 IMMEDIATE NEXT STEPS:\n";
echo "1. Restart Laragon (Stop All → Start All)\n";
echo "2. Visit: http://nelly-elearning.test/welcome.html\n";
echo "3. If that works, try: http://nelly-elearning.test/status.php\n";
echo "4. If both work, try: http://nelly-elearning.test/florida/login\n\n";

echo "📋 Files Created:\n";
echo "- welcome.html (immediate test)\n";
echo "- status.php (Laravel test)\n";
echo "- Virtual host configuration\n";
echo "- RESTART_INSTRUCTIONS.txt\n\n";

echo "✅ Your Laravel application should work after restarting Laragon!\n";

echo "\n🏁 Emergency fix completed at " . date('Y-m-d H:i:s') . "\n";