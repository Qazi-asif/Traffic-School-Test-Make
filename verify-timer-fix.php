<?php
/**
 * Timer Fix Verification Script
 * Run this on your production server to verify the fix is deployed correctly
 * 
 * Usage: php verify-timer-fix.php
 * Or visit: https://yourdomain.com/verify-timer-fix.php
 */

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Timer Fix Verification</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        .success { color: #28a745; font-weight: bold; }
        .error { color: #dc3545; font-weight: bold; }
        .warning { color: #ffc107; font-weight: bold; }
        .info { color: #17a2b8; }
        .section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 3px; overflow-x: auto; }
        h2 { border-bottom: 2px solid #007bff; padding-bottom: 10px; }
    </style>
</head>
<body>
    <h1>🔍 Timer Fix Verification</h1>
    <p>This script checks if the timer fix has been deployed correctly.</p>

    <?php
    $errors = [];
    $warnings = [];
    $success = [];

    // Check 1: Verify strict-timer.js exists
    echo '<div class="section">';
    echo '<h2>1. File Existence Check</h2>';
    
    $strictTimerPath = __DIR__ . '/public/js/strict-timer.js';
    if (file_exists($strictTimerPath)) {
        echo '<p class="success">✓ strict-timer.js found</p>';
        $success[] = 'strict-timer.js exists';
        
        // Check file modification time
        $modTime = filemtime($strictTimerPath);
        $modDate = date('Y-m-d H:i:s', $modTime);
        $hoursSinceModified = (time() - $modTime) / 3600;
        
        echo "<p class='info'>Last modified: $modDate";
        if ($hoursSinceModified < 1) {
            echo " <span class='success'>(Modified recently - good!)</span>";
        } elseif ($hoursSinceModified < 24) {
            echo " <span class='warning'>(Modified {$hoursSinceModified} hours ago)</span>";
        } else {
            echo " <span class='warning'>(Modified " . round($hoursSinceModified/24, 1) . " days ago - may be old)</span>";
        }
        echo '</p>';
        
        // Check file size
        $fileSize = filesize($strictTimerPath);
        echo "<p class='info'>File size: " . number_format($fileSize) . " bytes</p>";
        
    } else {
        echo '<p class="error">✗ strict-timer.js NOT FOUND at: ' . $strictTimerPath . '</p>';
        $errors[] = 'strict-timer.js not found';
    }
    
    $coursePlayerPath = __DIR__ . '/resources/views/course-player.blade.php';
    if (file_exists($coursePlayerPath)) {
        echo '<p class="success">✓ course-player.blade.php found</p>';
        $success[] = 'course-player.blade.php exists';
        
        $modTime = filemtime($coursePlayerPath);
        $modDate = date('Y-m-d H:i:s', $modTime);
        echo "<p class='info'>Last modified: $modDate</p>";
    } else {
        echo '<p class="error">✗ course-player.blade.php NOT FOUND</p>';
        $errors[] = 'course-player.blade.php not found';
    }
    echo '</div>';

    // Check 2: Verify strict-timer.js contains the fix
    echo '<div class="section">';
    echo '<h2>2. Code Content Check</h2>';
    
    if (file_exists($strictTimerPath)) {
        $strictTimerContent = file_get_contents($strictTimerPath);
        
        // Check for updateActionButtons in enableNextStep
        if (strpos($strictTimerContent, 'updateActionButtons') !== false) {
            echo '<p class="success">✓ updateActionButtons() found in strict-timer.js</p>';
            $success[] = 'updateActionButtons call exists';
            
            // Show the relevant code snippet
            if (preg_match('/enableNextStep\(\).*?\{(.*?)\}/s', $strictTimerContent, $matches)) {
                echo '<p class="info">Code snippet:</p>';
                echo '<pre>' . htmlspecialchars(trim($matches[0])) . '</pre>';
            }
        } else {
            echo '<p class="error">✗ updateActionButtons() NOT FOUND in strict-timer.js</p>';
            echo '<p class="warning">The fix may not be deployed correctly!</p>';
            $errors[] = 'updateActionButtons not found in strict-timer.js';
        }
    }
    
    if (file_exists($coursePlayerPath)) {
        $coursePlayerContent = file_get_contents($coursePlayerPath);
        
        // Check for updateActionButtons in timer completion
        if (strpos($coursePlayerContent, 'updateActionButtons()') !== false) {
            echo '<p class="success">✓ updateActionButtons() found in course-player.blade.php</p>';
            $success[] = 'updateActionButtons call in course-player';
        } else {
            echo '<p class="error">✗ updateActionButtons() NOT FOUND in course-player.blade.php</p>';
            $errors[] = 'updateActionButtons not found in course-player';
        }
        
        // Check for cache busting
        if (strpos($coursePlayerContent, 'strict-timer.js?v=') !== false) {
            echo '<p class="success">✓ Cache busting parameter found</p>';
            $success[] = 'Cache busting enabled';
        } else {
            echo '<p class="warning">⚠ Cache busting parameter NOT FOUND</p>';
            echo '<p class="info">This may cause browser caching issues</p>';
            $warnings[] = 'No cache busting';
        }
        
        // Check for old function name (should NOT exist)
        if (strpos($coursePlayerContent, 'displayActionButtons()') !== false) {
            echo '<p class="error">✗ Old function name "displayActionButtons()" still found!</p>';
            echo '<p class="warning">This will cause errors. The fix was not applied correctly.</p>';
            $errors[] = 'Old function name still present';
        } else {
            echo '<p class="success">✓ Old function name removed</p>';
            $success[] = 'Old function name removed';
        }
    }
    echo '</div>';

    // Check 3: PHP and Laravel environment
    echo '<div class="section">';
    echo '<h2>3. Environment Check</h2>';
    
    echo '<p class="info">PHP Version: ' . phpversion() . '</p>';
    
    // Check if Laravel is available
    if (file_exists(__DIR__ . '/artisan')) {
        echo '<p class="success">✓ Laravel detected</p>';
        
        // Check cache directories
        $cacheDirs = [
            'bootstrap/cache' => __DIR__ . '/bootstrap/cache',
            'storage/framework/cache' => __DIR__ . '/storage/framework/cache',
            'storage/framework/views' => __DIR__ . '/storage/framework/views',
        ];
        
        foreach ($cacheDirs as $name => $path) {
            if (is_dir($path) && is_writable($path)) {
                echo "<p class='success'>✓ $name is writable</p>";
            } else {
                echo "<p class='error'>✗ $name is not writable or doesn't exist</p>";
                $errors[] = "$name not writable";
            }
        }
    }
    
    // Check OPcache status
    if (function_exists('opcache_get_status')) {
        $opcache = opcache_get_status();
        if ($opcache && $opcache['opcache_enabled']) {
            echo '<p class="warning">⚠ OPcache is enabled</p>';
            echo '<p class="info">You may need to clear OPcache after deploying changes</p>';
            $warnings[] = 'OPcache enabled';
        } else {
            echo '<p class="info">OPcache is disabled or not available</p>';
        }
    }
    echo '</div>';

    // Summary
    echo '<div class="section">';
    echo '<h2>📊 Summary</h2>';
    
    echo '<p><strong>Successes:</strong> ' . count($success) . '</p>';
    if ($success) {
        echo '<ul>';
        foreach ($success as $item) {
            echo "<li class='success'>$item</li>";
        }
        echo '</ul>';
    }
    
    if ($warnings) {
        echo '<p><strong>Warnings:</strong> ' . count($warnings) . '</p>';
        echo '<ul>';
        foreach ($warnings as $item) {
            echo "<li class='warning'>$item</li>";
        }
        echo '</ul>';
    }
    
    if ($errors) {
        echo '<p><strong>Errors:</strong> ' . count($errors) . '</p>';
        echo '<ul>';
        foreach ($errors as $item) {
            echo "<li class='error'>$item</li>";
        }
        echo '</ul>';
    }
    
    if (empty($errors)) {
        echo '<h3 class="success">✅ All checks passed!</h3>';
        echo '<p>The timer fix appears to be deployed correctly.</p>';
        echo '<p><strong>Next steps:</strong></p>';
        echo '<ol>';
        echo '<li>Clear Laravel caches: <code>php artisan cache:clear && php artisan view:clear</code></li>';
        echo '<li>Clear browser cache (Ctrl+Shift+Delete)</li>';
        echo '<li>Test the timer functionality</li>';
        echo '</ol>';
    } else {
        echo '<h3 class="error">❌ Issues detected</h3>';
        echo '<p>Please fix the errors above before testing.</p>';
    }
    echo '</div>';

    // Recommended actions
    echo '<div class="section">';
    echo '<h2>🔧 Recommended Actions</h2>';
    echo '<ol>';
    echo '<li>Run: <code>php artisan cache:clear</code></li>';
    echo '<li>Run: <code>php artisan config:clear</code></li>';
    echo '<li>Run: <code>php artisan view:clear</code></li>';
    echo '<li>Run: <code>php artisan config:cache</code></li>';
    echo '<li>Run: <code>php artisan view:cache</code></li>';
    echo '<li>Clear browser cache (Ctrl+Shift+Delete)</li>';
    echo '<li>Hard refresh the page (Ctrl+F5)</li>';
    echo '</ol>';
    echo '</div>';
    ?>

    <div class="section">
        <h2>🗑️ Delete This File</h2>
        <p class="warning">⚠️ <strong>Important:</strong> Delete this verification script after use for security reasons.</p>
        <p>Run: <code>rm verify-timer-fix.php</code></p>
    </div>
</body>
</html>
