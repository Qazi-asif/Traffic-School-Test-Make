<?php

// Fix all CSRF token JavaScript calls in create-course.blade.php
$filePath = 'resources/views/create-course.blade.php';

if (!file_exists($filePath)) {
    echo "File not found: $filePath\n";
    exit(1);
}

$content = file_get_contents($filePath);

// Replace all instances of the unsafe CSRF token access
$oldPattern = "document.querySelector('meta[name=\"csrf-token\"]').getAttribute('content')";
$newPattern = "getSafeCSRFToken()";

$newContent = str_replace($oldPattern, $newPattern, $content);

// Count replacements
$replacements = substr_count($content, $oldPattern);

if ($replacements > 0) {
    file_put_contents($filePath, $newContent);
    echo "✅ Successfully replaced $replacements instances of unsafe CSRF token access\n";
    echo "✅ All CSRF token calls now use getSafeCSRFToken() function\n";
} else {
    echo "ℹ️  No unsafe CSRF token calls found\n";
}

echo "✅ CSRF JavaScript fix completed\n";

?>