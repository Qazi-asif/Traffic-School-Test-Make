<?php
/**
 * Diagnose Laravel Error
 */

echo "🔍 DIAGNOSING LARAVEL ERROR\n";
echo "===========================\n\n";

// Check if routes file has syntax errors
echo "1. CHECKING ROUTES FILE SYNTAX:\n";
echo "===============================\n";

$routesFile = __DIR__ . '/routes/web.php';
if (!file_exists($routesFile)) {
    echo "❌ Routes file not found\n";
    exit(1);
}

// Check PHP syntax
$output = [];
$returnCode = 0;
exec("php -l \"$routesFile\" 2>&1", $output, $returnCode);

if ($returnCode === 0) {
    echo "✅ Routes file syntax is valid\n";
} else {
    echo "❌ Routes file has syntax errors:\n";
    foreach ($output as $line) {
        echo "   $line\n";
    }
    exit(1);
}

// Check file size and content
$fileSize = filesize($routesFile);
echo "✅ Routes file size: " . number_format($fileSize) . " bytes\n";

$content = file_get_contents($routesFile);
$lineCount = substr_count($content, "\n");
echo "✅ Routes file lines: $lineCount\n";

// Check for common issues
echo "\n2. CHECKING FOR COMMON ISSUES:\n";
echo "===============================\n";

// Check for unclosed braces
$openBraces = substr_count($content, '{');
$closeBraces = substr_count($content, '}');
echo "Open braces: $openBraces, Close braces: $closeBraces\n";

if ($openBraces === $closeBraces) {
    echo "✅ Braces are balanced\n";
} else {
    echo "❌ Braces are NOT balanced\n";
}

// Check for unclosed quotes
$singleQuotes = substr_count($content, "'");
$doubleQuotes = substr_count($content, '"');
echo "Single quotes: $singleQuotes, Double quotes: $doubleQuotes\n";

// Check for Git conflict markers
if (strpos($content, '<<<<<<<') !== false || strpos($content, '>>>>>>>') !== false) {
    echo "❌ Git conflict markers found\n";
} else {
    echo "✅ No Git conflict markers\n";
}

// Check Laravel bootstrap
echo "\n3. CHECKING LARAVEL BOOTSTRAP:\n";
echo "===============================\n";

try {
    if (file_exists(__DIR__ . '/vendor/autoload.php')) {
        echo "✅ Composer autoload exists\n";
        require_once __DIR__ . '/vendor/autoload.php';
        echo "✅ Composer autoload loaded\n";
        
        if (file_exists(__DIR__ . '/bootstrap/app.php')) {
            echo "✅ Laravel bootstrap exists\n";
            $app = require_once __DIR__ . '/bootstrap/app.php';
            echo "✅ Laravel app created\n";
            
            // Try to get the kernel
            $kernel = $app->make('Illuminate\Contracts\Console\Kernel');
            echo "✅ Console kernel created\n";
            
        } else {
            echo "❌ Laravel bootstrap not found\n";
        }
    } else {
        echo "❌ Composer autoload not found\n";
    }
} catch (Exception $e) {
    echo "❌ Laravel bootstrap error: " . $e->getMessage() . "\n";
}

echo "\n🎯 DIAGNOSIS COMPLETE\n";
echo "=====================\n";
echo "If routes syntax is valid but Laravel still fails,\n";
echo "the issue is likely in the Laravel configuration or cache.\n";
echo "\nTry accessing: http://nelly-elearning.test/test-basic\n";
?>