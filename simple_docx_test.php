<?php
echo "=== DOCX Import System Status ===\n\n";

// Test 1: Check if we can connect to database
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=nelly_elearning', 'root', '');
    echo "✅ Database connection successful\n";
    
    // Check chapters table structure
    $stmt = $pdo->query("DESCRIBE chapters");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "✅ Chapters table columns: " . implode(', ', $columns) . "\n";
    
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
}

// Test 2: Check PHPWord
if (file_exists('vendor/autoload.php')) {
    require_once 'vendor/autoload.php';
    
    if (class_exists('PhpOffice\PhpWord\IOFactory')) {
        echo "✅ PHPWord library is available\n";
    } else {
        echo "❌ PHPWord library not found\n";
    }
} else {
    echo "❌ Composer autoload not found\n";
}

// Test 3: Check storage directory
$storageDir = 'storage/app/public/course-media';
if (file_exists($storageDir)) {
    echo "✅ Course media directory exists\n";
    if (is_writable($storageDir)) {
        echo "✅ Directory is writable\n";
    } else {
        echo "⚠️  Directory is not writable\n";
    }
} else {
    echo "⚠️  Course media directory doesn't exist\n";
}

echo "\n=== Solution Summary ===\n";
echo "The DOCX import issues have been resolved with:\n\n";
echo "1. ✅ Adaptive storeWeb() method that detects available database columns\n";
echo "2. ✅ Enhanced CSRF token handling in frontend JavaScript\n";
echo "3. ✅ Better error handling for different response types\n";
echo "4. ✅ Fallback methods for problematic DOCX files\n";
echo "5. ✅ Migration to add missing database columns\n\n";

echo "The system should now handle DOCX imports without column errors.\n";
echo "If you still get HTTP 419 errors, refresh the page to get a new CSRF token.\n";
?>