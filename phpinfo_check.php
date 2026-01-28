<?php
/**
 * PHP Configuration Check
 */

echo "🔍 PHP CONFIGURATION CHECK\n";
echo "===========================\n\n";

echo "PHP Version: " . PHP_VERSION . "\n";
echo "PHP SAPI: " . php_sapi_name() . "\n";
echo "Server Software: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "\n";

echo "\n📋 REQUIRED EXTENSIONS:\n";
echo "=======================\n";

$requiredExtensions = [
    'mbstring',
    'openssl', 
    'pdo',
    'tokenizer',
    'xml',
    'ctype',
    'json',
    'bcmath',
    'fileinfo'
];

foreach ($requiredExtensions as $ext) {
    if (extension_loaded($ext)) {
        echo "✅ $ext\n";
    } else {
        echo "❌ $ext (MISSING)\n";
    }
}

echo "\n⚙️ PHP SETTINGS:\n";
echo "================\n";

$settings = [
    'memory_limit' => ini_get('memory_limit'),
    'max_execution_time' => ini_get('max_execution_time'),
    'upload_max_filesize' => ini_get('upload_max_filesize'),
    'post_max_size' => ini_get('post_max_size'),
    'display_errors' => ini_get('display_errors') ? 'On' : 'Off',
    'log_errors' => ini_get('log_errors') ? 'On' : 'Off'
];

foreach ($settings as $setting => $value) {
    echo "$setting: $value\n";
}

echo "\n🌐 SIMPLE HTML TEST:\n";
echo "====================\n";
?>
<!DOCTYPE html>
<html>
<head>
    <title>PHP Test</title>
</head>
<body>
    <h1>✅ PHP is working!</h1>
    <p>If you can see this, PHP is processing correctly.</p>
    <p>Time: <?php echo date('Y-m-d H:i:s'); ?></p>
    <p><a href="/test-emergency">Test Emergency Route</a></p>
    <p><a href="/florida">Test Florida Route</a></p>
</body>
</html>