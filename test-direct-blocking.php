<?php

// Simple PHP test - put this at the TOP of public/index.php to test blocking

$currentPath = $_SERVER['REQUEST_URI'] ?? '';

// Remove query parameters
$currentPath = strtok($currentPath, '?');

echo "🔍 TESTING DIRECT BLOCKING\n";
echo "Current path: " . $currentPath . "\n";

// Test if we can block at PHP level
if ($currentPath === '/dashboard' || strpos($currentPath, '/admin') === 0) {
    echo "🚫 WOULD BLOCK: " . $currentPath . "\n";
    
    // This would block the request
    // header('HTTP/1.1 503 Service Unavailable');
    // echo '<h1>Service Temporarily Unavailable</h1>';
    // exit;
} else {
    echo "✅ WOULD ALLOW: " . $currentPath . "\n";
}

echo "\n💡 INSTRUCTIONS:\n";
echo "1. If you see this message, PHP blocking would work\n";
echo "2. We can implement this in Laravel middleware\n";
echo "3. The issue might be middleware not being called\n";

echo "\n🔧 NEXT STEPS:\n";
echo "1. Try the DirectBlockMiddleware I just created\n";
echo "2. Check Laravel logs for middleware messages\n";
echo "3. If still not working, we'll use route-level blocking\n";

echo "\n✅ Test completed!\n";